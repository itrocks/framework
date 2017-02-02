<?php
namespace ITRocks\Framework\PHP;

use ITRocks\Framework\Application;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Needs_Main;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Set;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Router;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Files;
use ITRocks\Framework\Tools\List_Row;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\Updater\Application_Updater;
use ITRocks\Framework\Updater\Updatable;
use Serializable;

/**
 * Php compiler : the php scripts compilers manager
 *
 * This has heavy dependencies to the ITRocks Framework, and can't be used without it at the moment
 */
class Compiler implements
	Class_File_Name_Getter, Configurable, Needs_Main, Registerable, Serializable, Updatable
{

	//------------------------------------------------------------------------------------ $cache_dir
	/**
	 * Cache directory name
	 *
	 * You should not use this property.
	 * Please use getCacheDir() to be sure this is initialized.
	 *
	 * @var string
	 */
	private $cache_dir;

	//------------------------------------------------------------------------------------- $compiler
	/**
	 * Currently used compiler
	 *
	 * @var ICompiler
	 */
	private $compiler;

	//------------------------------------------------------------------------------------ $compilers
	/**
	 * The list of compilers used successively on PHP sources
	 *
	 * Compilation process is : all compilers of wave 1 for each file, then all compilers of wave 2,
	 * etc.
	 *
	 * @var array ICompiler[integer $wave_number][string $class_name]
	 */
	private $compilers = [];

	//------------------------------------------------------------------------------ $main_controller
	/**
	 * The main controller that called the compilation process
	 *
	 * @var Main
	 */
	public $main_controller;

	//--------------------------------------------------------------------------------- $more_sources
	/**
	 * List of PHP sources to be compiled on next wave
	 *
	 * @var Reflection_Source[]
	 */
	private $more_sources = [];

	//-------------------------------------------------------------------------------- $saved_sources
	/**
	 * Saved sources : during the second and next compiler passes, all compiled sources are saved
	 * here
	 *
	 * @var Reflection_Source[]
	 */
	private $saved_sources = [];

	//-------------------------------------------------------------------------------------- $sources
	/**
	 * List of PHP sources being compiled.
	 *
	 * This is set only when into compile() process. Outside of it, this property is null.
	 *
	 * @var Reflection_Source[]
	 */
	private $sources;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * This constructor zaps the cache directory if 'Z' argument is sent
	 * This will result into a complete application cache rebuild
	 *
	 * @param $configuration array string[integer $wave_number][]
	 */
	public function __construct($configuration = [])
	{
		foreach ($configuration as $wave_number => $compilers) {
			foreach ($compilers as $class_name) {
				$this->compilers[$wave_number][$class_name]
					= Session::current()->plugins->has($class_name)
					? Session::current()->plugins->get($class_name)
					: Builder::create($class_name);
			}
		}
		if (isset($_GET['Z'])) {
			$cache_dir = $this->getCacheDir();
			if ($cache_dir && is_dir($cache_dir)) {
				system('rm -rf ' . $cache_dir . '/*');
			}
		}
	}

	//----------------------------------------------------------------------------------- addCompiler
	/**
	 * @param $compiler ICompiler
	 */
	public function addCompiler(ICompiler $compiler)
	{
		$this->compilers[] = $compiler;
	}

	//------------------------------------------------------------------------------------- addSource
	/**
	 * Adds a PHP source to the sources to be compiled
	 * This can be called before or during the compilation process, so these new files will be
	 * compiled at the next compilation wave, when all current listed sources are compiled.
	 *
	 * @param $source Reflection_Source
	 */
	public function addSource(Reflection_Source $source)
	{
		/** @var Reflection_Source[] $new_sources_to_compile */
		if (!isset($source->file_name)) {
			$classes = $source->getClasses();
			if ($classes) {
				$class = reset($classes);
				$source->file_name = $this->getCacheDir()
					. SL . str_replace(SL, '-', Names::classToPath($class->name));
			}
			else {
				trigger_error(
					'You should only compile php scripts containing a class'
					. ' (' . $source->file_name . ' compiled with ' . get_class($this->compiler) . ')',
					E_USER_ERROR
				);
			}
		}
		$this->more_sources[$source->getFirstClassName() ?: $source->file_name] = $source;
	}

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $last_time integer compile only files modified since this time
	 */
	public function compile($last_time = 0)
	{
		upgradeTimeLimit(900);
		clearstatcache();
		$cache_dir = $this->getCacheDir();

		// create data set for dependencies, check for dependencies for deleted files
		Dao::createStorage(Dependency::class);
		Dao::begin();
		if (isset($_GET['Z'])) {
			Dao::truncate(Dependency::class);
		}
		else {
			foreach (
				Dao::select(Dependency::class, ['file_name' => Func::distinct()]) as $file_dependency
			) {
				/** @var $file_dependency List_Row */
				$file_name = $file_dependency->getValue('file_name');
				if (!file_exists($file_name)) {
					foreach (Dao::search(['file_name' => $file_name], Dependency::class) as $dependency) {
						/** @var $dependency Dependency */
						Dao::delete($dependency);
						foreach (
							Dao::search(['dependency_name' => $dependency->class_name], Dependency::class)
							as $sub_dependency
						) {
							/** @var $sub_dependency Dependency */
							Dao::delete($sub_dependency);
						}
					}
				}
			}
		}
		Dao::commit();

		$this->sources = array_merge($this->more_sources, $this->getFilesToCompile($last_time));
		$first_group = true;

		foreach ($this->compilers as $compilers) {
			/** @var $compilers ICompiler[] */

			$this->saved_sources = $this->sources;
			while ($this->sources) {

				// get source and update dependencies
				foreach ($this->sources as $source) {
					$this->replaceDependencies($source);
				}

				do {
					$added = [];

					// ask each compiler for adding of compiled files, until they have nothing to add
					foreach ($compilers as $compiler) {
						if ($compiler instanceof Needs_Main) {
							$compiler->setMainController($this->main_controller);
						}
						$added = array_merge($added, $compiler->moreSourcesToCompile($this->sources));
					}

					foreach ($added as $added_key => $source) {
						$source_file_name = $source->getFirstClassName() ?: $source->file_name;
						if (isset($this->sources[$source_file_name])) {
							unset($added[$added_key]);
						}
						else {
							$this->replaceDependencies($source);
							$this->sources[$source_file_name] = $source;
						}
					}

					if (count($compilers) == 1) {
						$added = [];
					}

				} while ($added);

				$this->saved_sources = array_merge($this->saved_sources, $this->sources);

				// compile sources
				$this->sortSourcesByParentsCount();
				foreach ($this->sources as $source) {
					$this->compileSource($source, $compilers, $cache_dir, $first_group);
				}

				$this->sources = $this->more_sources;
				$this->more_sources = [];
				foreach ($this->sources as $source_class_name => $source) {
					if (!isset($this->saved_sources[$source_class_name])) {
						$this->saved_sources[$source_class_name] = $source;
					}
				}
			}
			$this->sources = $this->saved_sources;
			$first_group = false;
		}
		$this->sources = null;

	}

	//--------------------------------------------------------------------------------- compileSource
	/**
	 * Compile one source file using compilers
	 *
	 * @param $source      Reflection_Source
	 * @param $compilers   ICompiler[]
	 * @param $cache_dir   string
	 * @param $first_group boolean
	 */
	private function compileSource(
	 	Reflection_Source $source, array $compilers, $cache_dir, $first_group
	) {
		foreach ($compilers as $compiler) {
			if (isset($GLOBALS['D'])) {
				echo get_class($compiler) . ' : Compile source file ' . $source->file_name
					. ' class ' . $source->getFirstClassName() . SP . BR . LF;
			}
			$compiler->compile($source, $this);
		}
		$file_name = (substr($source->file_name, 0, strlen($cache_dir)) === $cache_dir)
			? $source->file_name
			: ($this->getCacheDir() . SL . str_replace(SL, '-', substr($source->file_name, 0, -4)));
		if ($source->hasChanged()) {
			$this->replaceDependencies($source);
			script_put_contents($file_name, $source->getSource());
		}
		elseif (file_exists($file_name) && $first_group) {
			unlink($file_name);
		}
	}

	//----------------------------------------------------------------------------------- getCacheDir
	/**
	 * @return string
	 */
	public function getCacheDir()
	{
		if (!isset($this->cache_dir)) {
			$this->cache_dir = Application::current()->getCacheDir() . '/compiled';
			Files::mkdir($this->cache_dir);
		}
		return Paths::getRelativeFileName($this->cache_dir);
	}

	//------------------------------------------------------------------------------ getClassFileName
	/**
	 * Gets a Reflection_Source knowing its class _name.
	 * Uses sources cache, or router's getClassFileName() and fill-in cache.
	 ** @param $class_name string
	 * @return Reflection_Source
	 */
	public function getClassFileName($class_name)
	{
		if (isset($this->saved_sources[$class_name])) {
			return $this->saved_sources[$class_name];
		}
		else {
			/** @var $router Router */
			$router = Session::current()->plugins->get(Router::class);
			if (Builder::isBuilt($class_name)) {
				$file_name = $this->getCacheDir() . SL
					. str_replace(SL, '-', Names::classToPath($class_name));
			}
			else {
				$file_name = $router->getClassFileName($class_name);
			}
			return Reflection_Source::ofFile($file_name, $class_name);
		}
	}

	//----------------------------------------------------------------------------- getFilesToCompile
	/**
	 * Gets the list of php files that were modifier since $last_time
	 *
	 * @param $last_time integer scan only files modified since this time
	 * @return Reflection_Source[] key is full file path, value is file name
	 */
	private function getFilesToCompile($last_time = 0)
	{
		$source_files = Application::current()->include_path->getSourceFiles();
		foreach (scandir(DOT) as $file_name) {
			$source_files[] = $file_name;
		}
		$files = [];
		foreach ($source_files as $file_path) {
			if ((substr($file_path, -4) == '.php') && (filemtime($file_path) > $last_time)) {
				$source = Reflection_Source::ofFile($file_path);
				$files[$source->getFirstClassName() ?: $file_path] = $source;
			}
		}
		return $files;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		/** @var $application_updater Application_Updater */
		$application_updater = Session::current()->plugins->get(Application_Updater::class);
		$application_updater->addUpdatable($this);
	}

	//--------------------------------------------------------------------------- replaceDependencies
	/**
	 * @param $source Reflection_Source
	 */
	private function replaceDependencies(Reflection_Source $source)
	{
		$dependencies = $source->getDependencies(true);
		foreach ($dependencies as $dependency) {
			if ($dependency->type === Dependency::T_STORE) {
				$store_is_set[$dependency->class_name] = true;
			}
		}
		foreach ($source->getClasses() as $class) {
			if (
				!isset($store_is_set[$class->name])
				&& !$class->isAbstract()
				&& $class->getAnnotation('business')->value
			) {
				$dependency = new Dependency();
				$dependency->class_name = $class->name;
				$dependency->dependency_name = strtolower(
					Namespaces::shortClassName($class->getAnnotation('set')->value)
				);
				$dependency->file_name = $source->file_name;
				$dependency->type = Dependency::T_STORE;
				$dependencies[] = $dependency;
			}
		}
		/** @noinspection PhpParamsInspection inspector bug (a Dependency is an object) */
		(new Set)->replace(
			$dependencies, Dependency::class, ['file_name' => Func::equal($source->file_name)]
		);
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * @return string
	 */
	public function serialize()
	{
		$serialized_compilers = [];
		foreach ($this->compilers as $wave_number => $compilers) {
			foreach ($compilers as $compiler) {
				$serialized_compilers[$wave_number][] = is_object($compiler) ? get_class($compiler) : $compiler;
			}
		}
		return serialize($serialized_compilers);
	}

	//----------------------------------------------------------------------------- setMainController
	/**
	 * @param $main_controller Main
	 */
	public function setMainController(Main $main_controller)
	{
		$this->main_controller = $main_controller;
	}

	//--------------------------------------------------------------------- sortSourcesByParentsCount
	/**
	 * Parent classes must be compiled first : so sort classes by :
	 * - first the classes without any parent
	 * - next the classes with 1 parent
	 * - and so on...
	 */
	private function sortSourcesByParentsCount()
	{
		$by_parents_count = [];
		foreach ($this->sources as $source_class_name => $source) {
			// -1 : no class in source, 0 : no parent, 1 : one parent, etc.
			$parents_count = -1;
			$parent_class = $source->getClasses();
			$parent_class = reset($parent_class);
			while (
				$parent_class
				&& ($parent_class instanceof Reflection_Class)
				&& !$parent_class->isInternal()
			) {
				$parents_count ++;
				$parent_name = $parent_class->getParentName();
				flush();
				if ($parent_name) {
					if (isset($this->saved_sources[$parent_name])) {
						$parent_class = $this->saved_sources[$parent_name]->getClasses();
						$parent_class = reset($parent_class);
					}
					else {
						$file_name = $this->getClassFileName($parent_name);
						if ($file_name instanceof Reflection_Source) {
							if ($file_name->isInternal()) {
								break;
							}
							$file_name = $file_name->file_name;
						}
						if (isset($this->saved_sources[$file_name])) {
							$parent_class = $this->saved_sources[$file_name]->getClasses();
							$parent_class = reset($parent_class);
						}
						else {
							$parent_class = $parent_class->getParentClass();
						}
					}
				}
				else {
					break;
				}
			}
			$by_parents_count[$parents_count][$source_class_name] = $source;
		}
		ksort($by_parents_count);
		$this->sources = [];
		foreach ($by_parents_count as $sources) {
			$this->sources = array_merge($this->sources, $sources);
		}
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * @param $last_time integer
	 */
	public function update($last_time = 0)
	{
		$this->compile($last_time);
	}

	//----------------------------------------------------------------------------------- unserialize
	/**
	 * @param $serialized string
	 */
	public function unserialize($serialized)
	{
		$this->compilers = [];
		foreach (unserialize($serialized) as $wave_number => $compilers) {
			foreach ($compilers as $class_name) {
				$this->compilers[$wave_number][] = Session::current()->plugins->has($class_name)
					? Session::current()->plugins->get($class_name)
					: Builder::create($class_name);
			}
		}
	}

}
