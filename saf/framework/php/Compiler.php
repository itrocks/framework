<?php
namespace SAF\Framework\PHP;

use SAF\Framework\Application;
use SAF\Framework\Builder;
use SAF\Framework\Controller\Main;
use SAF\Framework\Controller\Needs_Main;
use SAF\Framework\Dao;
use SAF\Framework\Dao\Func;
use SAF\Framework\Dao\Mysql\Link;
use SAF\Framework\Dao\Set;
use SAF\Framework\Plugin\Configurable;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Reflection;
use SAF\Framework\Router;
use SAF\Framework\Session;
use SAF\Framework\Tools\Files;
use SAF\Framework\Tools\List_Row;
use SAF\Framework\Tools\Names;
use SAF\Framework\Updater\Application_Updater;
use SAF\Framework\Updater\Updatable;
use Serializable;

/**
 * Php compiler : the php scripts compilers manager
 *
 * This has heavy dependencies to the SAF Framework, and can't be used without it at the moment
 */
class Compiler implements
	Configurable, Registerable, Class_File_Name_Getter, Needs_Main, Serializable, Updatable
{

	const MAX_OPENED_SOURCES = 100;

	const SOURCES_FREE = 10;

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

	//-------------------------------------------------------------------------------------- $sources
	/**
	 * List of PHP sources being compiled.
	 *
	 * This is set only when into compile() process. Outside of it, this property is null.
	 *
	 * @var Reflection_Source[]
	 */
	private $sources;

	//-------------------------------------------------------------------------------- $sources_cache
	/**
	 * @var Reflection_Source[]
	 */
	private $sources_cache = [];

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

	//----------------------------------------------------------------------------------- __construct
	/**
	 * This constructor zaps the cache directory if 'Z' argument is sent
	 * This will result into a complete application cache rebuild
	 *
	 * @param $configuration array string[integer $wave_number][]
	 */
	public function __construct($configuration = null)
	{
		if (isset($configuration)) {
			foreach ($configuration as $wave_number => $compilers) {
				foreach ($compilers as $class_name) {
					$this->compilers[$wave_number][$class_name]
						= Session::current()->plugins->has($class_name)
						? Session::current()->plugins->get($class_name)
						: Builder::create($class_name);
				}
			}
		}
		if (isset($_GET['Z'])) {
			$cache_dir = $this->getCacheDir();
			system('rm -rf ' . $cache_dir . '/*');
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
		foreach (array_keys($source->getClasses()) as $class_name) {
			$this->sources_cache[$class_name] = $source;
		}
		$this->more_sources[$source->file_name] = $source;

		if (count($this->sources_cache) > 1000) {
			$source->free(self::SOURCES_FREE);
		}
	}

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $last_time integer compile only files modified since this time
	 */
	public function compile($last_time = 0)
	{
		set_time_limit(900);
		clearstatcache();
		$cache_dir = $this->getCacheDir();

		// create data set for dependencies, check for dependencies for deleted files
		Dao::createStorage(Dependency::class);
		Dao::begin();
		if (isset($_GET['Z'])) {
			$link = Dao::current();
			if ($link instanceof Link) {
				$link->query('TRUNCATE TABLE `dependencies`');
			}
		}
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
		Dao::commit();

		$this->sources = array_merge($this->more_sources, $this->getFilesToCompile($last_time));
		$first_group = true;

		foreach ($this->compilers as $compilers) {
			/** @var $compilers ICompiler[] */

			$saved_sources = $this->sources;
			while ($this->sources) {

				// get source and update dependencies
				foreach ($this->sources as $source) {
					/** @var Reflection_Source $source inspector bug */
					/** @noinspection PhpParamsInspection inspector bug (a Dependency is an object) */
					(new Set)->replace(
						$source->getDependencies(true),
						Dependency::class,
						['file_name' => $source->file_name]
					);
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

					foreach ($added as $source) {
						/** @var Reflection_Source $source inspector bug */
						/** @noinspection PhpParamsInspection inspector bug (a Dependency is an object) */
						(new Set)->replace(
							$source->getDependencies(true),
							Dependency::class,
							['file_name' => $source->file_name]
						);
						$this->sources[$source->file_name] = $source;
					}

					if (count($compilers) == 1) {
						$added = [];
					}

				} while ($added);

				$saved_sources = array_merge($saved_sources, $this->sources);

				// fill in sources cache
				$sources_count = count($this->sources);
				foreach ($this->sources as $source) {
					foreach (array_keys($source->getClasses()) as $class_name) {
						$this->sources_cache[$class_name] = $source;
					}
					if ($sources_count > self::MAX_OPENED_SOURCES) {
						$source->free(self::SOURCES_FREE);
					}
				}

				// compile sources
				foreach ($this->sources as $source) {
					$this->compileSource($source, $compilers, $cache_dir, $first_group, $sources_count);
				}

				$this->sources = $this->more_sources;
				$this->more_sources = [];
				foreach ($this->sources as $source) {
					if (!isset($saved_sources[$source->file_name])) {
						$saved_sources[$source->file_name] = $source;
					}
				}
			}
			$this->sources = $saved_sources;
			$first_group = false;
		}
		$this->sources = null;

	}

	//--------------------------------------------------------------------------------- compileSource
	/**
	 * Compile one source file using compilers
	 *
	 * @param $source        Reflection_Source
	 * @param $compilers     ICompiler[]
	 * @param $cache_dir     string
	 * @param $first_group   boolean
	 * @param $sources_count integer
	 * @return mixed
	 */
	private function compileSource(
		Reflection_Source $source, $compilers, $cache_dir, $first_group, $sources_count
	) {
		foreach ($compilers as $compiler) {
			$compiler->compile($source, $this);
		}
		$file_name = (substr($source->file_name, 0, strlen($cache_dir)) === $cache_dir)
			? $source->file_name
			: ($this->getCacheDir() . SL . str_replace(SL, '-', substr($source->file_name, 0, -4)));
		if ($source->hasChanged()) {
			/** @var Reflection_Source $source inspector bug */
			/** @noinspection PhpParamsInspection inspector bug (a Dependency is an object) */
			(new Set)->replace(
				$source->getDependencies(true),
				Dependency::class,
				['file_name' => $source->file_name]
			);
			script_put_contents($file_name, $source->getSource());
		}
		elseif (file_exists($file_name) && $first_group) {
			unlink($file_name);
		}
		if ($sources_count > self::MAX_OPENED_SOURCES) {
			$source->free(self::SOURCES_FREE);
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
		return $this->cache_dir;
	}

	//------------------------------------------------------------------------------ getClassFilename
	/**
	 * Gets a Reflection_Source knowing its class _name.
	 * Uses sources cache, or router's getClassFilename() and fill-in cache.
	 *
	 * @param $class_name string
	 * @return Reflection_Source
	 */
	public function getClassFilename($class_name)
	{
		if (isset($this->sources_cache[$class_name])) {
			return $this->sources_cache[$class_name];
		}
		else {
			/** @var $router Router */
			$router = Session::current()->plugins->get(Router::class);
			if (Builder::isBuilt($class_name)) {
				$file_name = $this->getCacheDir() . SL
					. str_replace(SL, '-', Names::classToPath($class_name));
			}
			else {
				$file_name = $router->getClassFilename($class_name);
			}
			$source = new Reflection_Source($file_name, $this, $class_name);
			foreach (array_keys($source->getClasses()) as $class_name) {
				$this->sources_cache[$class_name] = $source;
				if (count($this->sources_cache) > self::MAX_OPENED_SOURCES) {
					$source->free(self::SOURCES_FREE);
				}
			}
			return $this->sources_cache[$class_name];
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
				$files[$file_path] = new Reflection_Source($file_path, $this);
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
