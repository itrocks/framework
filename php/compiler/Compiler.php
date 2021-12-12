<?php
namespace ITRocks\Framework\PHP;

use ITRocks\Framework\AOP\Include_Filter;
use ITRocks\Framework\Application;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Builder\Class_Builder;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Needs_Main;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Set;
use ITRocks\Framework\Logger\Text_Output;
use ITRocks\Framework\PHP\Compiler\More_Sources;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection\Annotation\Class_\Store_Name_Annotation;
use ITRocks\Framework\Router;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Files;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Updater\Application_Updater;
use ITRocks\Framework\Updater\Updatable;

/**
 * Php compiler : the php scripts compilers manager
 *
 * This has heavy dependencies to the ITRocks Framework, and can't be used without it at the moment
 *
 * Note: given class name or source file name, the compiled file name should be able to be reversed
 *       to original class name or source file name.
 * So with:
 * Itrocks\Framework\Module\Class_Name, itrocks/framework/module/Class_Name.php
 * => vendor-application-itrocks-framework-module-Class_Name
 * Itrocks\Framework\Module\Class_Name, itrocks/framework/module/class_name/Class_Name.php
 * => vendor-application-itrocks-framework-module-Class_Name
 * We have direct reverse to class name!
 * To reverse source file we just have to check existence of both case file.
 */
class Compiler extends Cache
	implements Class_File_Name_Getter, Configurable, Needs_Main, Registerable, Updatable
{

	//-------------------------------------------------------------------------------- CACHE_DIR_NAME
	/**
	 * Basename of the cache directory
	 */
	const CACHE_DIR_NAME = 'compiled';

	//------------------------------------------------------------------------------------- $compiled
	/**
	 * Compiled files list : in order to avoid compiling the same file two times with the same
	 * compiler
	 *
	 * @var array true[integer $compilers_key][string $compiled_file_name]
	 */
	private $compiled = [];

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

	//---------------------------------------------------------------------------------- $has_changed
	/**
	 * Files which source code has been changed during compilation
	 *
	 * @var array true[string $file_name]
	 */
	private $has_changed = [];

	//------------------------------------------------------------------------------------ $last_wave
	/**
	 * true if the actually running compilation wave is the last wave
	 * unlink files is accepted only during this last compilation wave
	 *
	 * @var boolean
	 */
	private $last_wave;

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
	private array $sources;

	//---------------------------------------------------------------------------------- $text_output
	/**
	 * @var Text_Output
	 */
	private $text_output;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * This constructor zaps the cache directory if 'Z' argument is sent
	 * This will result into a complete application cache rebuild
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $configuration array string[integer $wave_number][]
	 */
	public function __construct($configuration = [])
	{
		$this->full        = isset($_GET['Z']);
		$this->text_output = new Text_Output(!isset($_POST['verbose']));

		foreach ($configuration as $wave_number => $compilers) {
			foreach ($compilers as $class_name) {
				/** @noinspection PhpUnhandledExceptionInspection valid compiler class name */
				$this->compilers[$wave_number][$class_name]
					= Session::current()->plugins->has($class_name)
					? Session::current()->plugins->get($class_name)
					: Builder::create($class_name);
			}
		}
		parent::manageCacheDirReset();
	}

	//----------------------------------------------------------------------------------- __serialize
	/**
	 * @return array
	 */
	public function __serialize() : array
	{
		$serialized_compilers = [];
		foreach ($this->compilers as $wave_number => $compilers) {
			foreach ($compilers as $compiler) {
				$serialized_compilers[$wave_number][] = is_object($compiler)
					? get_class($compiler)
					: $compiler;
			}
		}
		return $serialized_compilers;
	}

	//--------------------------------------------------------------------------------- __unserialize
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $serialized array
	 */
	public function __unserialize(array $serialized)
	{
		$this->compilers = [];
		foreach ($serialized as $wave_number => $compilers) {
			foreach ($compilers as $class_name) {
				/** @noinspection PhpUnhandledExceptionInspection valid compiler class name */
				$this->compilers[$wave_number][] = Session::current()->plugins->has($class_name)
					? Session::current()->plugins->get($class_name)
					: Builder::create($class_name);
			}
		}
		$this->text_output = new Text_Output(!isset($_POST['verbose']));
	}

	//----------------------------------------------------------------------------------- addCompiler
	/**
	 * @param $compiler ICompiler
	 */
	public function addCompiler(ICompiler $compiler)
	{
		$this->compilers[] = $compiler;
	}

	//----------------------------------------------------------------------- addMoreDependentSources
	/**
	 * @param $class_name string
	 */
	protected function addMoreDependentSources($class_name)
	{
		$more_sources = new More_Sources($this->more_sources);
		// add removed class descendants
		$search = [
			'class_name'      => Func::notEqual($class_name),
			'dependency_name' => Func::equal($class_name),
			'type'            => [Dependency::T_EXTENDS, Dependency::T_IMPLEMENTS, Dependency::T_USE]
		];
		foreach (Dao::search($search, Dependency::class) as $dependency) {
			/** @var $dependency Dependency */
			$more_sources->add(
				Reflection_Source::ofClass($dependency->class_name), $dependency->class_name, null, true
			);
		}
	}

	//-------------------------------------------------------------------------------- addMoreSources
	/**
	 * @param $compilers ICompiler[] ICompiler[string $class_name]
	 */
	private function addMoreSources(array $compilers)
	{
		do {
			$more_sources = new More_Sources($this->sources);

			// ask each compiler for adding of compiled files, until they have nothing to add
			foreach ($compilers as $compiler) {
				if ($compiler instanceof Needs_Main) {
					$compiler->setMainController($this->main_controller);
				}
				$compiler->moreSourcesToCompile($more_sources);
			}

			foreach ($more_sources->added as $added_key => $source) {
				$source_file_name = $source->getFirstClassName() ?: $source->file_name;
				if (isset($this->sources[$source_file_name])) {
					unset($more_sources->added[$added_key]);
				}
				else {
					$this->replaceDependencies($source);
					$this->sources[$source_file_name] = $source;
				}
			}

			if (count($compilers) == 1) {
				$more_sources->added = [];
			}

		}
		while ($more_sources->added);
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
				$class             = reset($classes);
				$source->file_name = self::classToCacheFilePath($class->name);
			}
			else {
				trigger_error(
					'You should only compile php scripts containing a class'
					. ' (' . $source->file_name . ' compiled with ' . get_class($this->compiler) . ')',
					E_USER_ERROR
				);
			}
		}
		// Don't do this with More_Sources, because add more only if not already into saved / sources
		// This works in 'existing source replacement mode'
		$source_key = $source->getFirstClassName() ?: $source->file_name;
		if (isset($this->saved_sources[$source_key])) {
			$this->saved_sources[$source_key] = $source;
			$added = true;
		}
		if (isset($this->sources[$source_key])) {
			$this->sources[$source_key] = $source;
			$added = true;
		}
		if (isset($this->more_sources[$source_key]) || !isset($added)) {
			$this->more_sources[$source_key] = $source;
		}
	}

	//-------------------------------------------------------------------------- cacheFileNameToClass
	/**
	 * Returns the class name given a compiled file name (excluding the cache dir part)
	 *
	 * @param $path string
	 * @return string
	 * @see Compiler::classToPath()
	 */
	public static function cacheFileNameToClass($path)
	{
		return Names::pathToClass(str_replace('-', SL, $path));
	}

	//--------------------------------------------------------------------- cacheFileNameToSourceFile
	/**
	 * Returns the source file given a compiled file path (excluding the cache dir part)
	 * eg. a-class-name-like-This into a/class/name/like/This.php or a/class/name/like/this/This.php
	 *
	 * @param $path string
	 * @return string|false false if source file not found
	 * @see Compiler::sourceFileToPath()
	 */
	public static function cacheFileNameToSourceFile(string $path) : string|false
	{
		return Names::classToFilePath(self::cacheFileNameToClass($path));
	}

	//-------------------------------------------------------------------------- classToCacheFilePath
	/**
	 * Returns the filename where to store cache-compiled file for given class name
	 * This does not include cache dir
	 *
	 * @param $class_name string
	 * @return string
	 * @see Compiler::pathToClass()
	 */
	public static function classToCacheFilePath($class_name)
	{
		return self::getCacheDir() . SL . str_replace('/', '-', Names::classToPath($class_name));
	}

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $last_time integer compile only files modified since this time
	 */
	public function compile($last_time = 0)
	{
		$this->text_output->log('<h1>Starting ' . ($this->full ? 'full' : '') . ' compile</h1>');
		upgradeTimeLimit(900);
		clearstatcache();
		$cache_dir = self::getCacheDir();

		$this->removeOldDependencies();

		$this->sources = array_merge($this->more_sources, $this->getFilesToCompile($last_time));

		$wave = 0;
		foreach ($this->compilers as $compilers_stop => $compilers) {
			$this->text_output->log('Wave ' . ++$wave . ' on ' . count($this->compilers));
			$this->text_output->log('Compilers : '. join(', ', array_keys($compilers)));
			$this->last_wave = ($wave === count($this->compilers));
			// save sources in oder to give them to next compilers too
			/** @var $compilers ICompiler[] */
			$this->saved_sources = $this->sources;
			while ($this->sources) {
				$this->replaceDependenciesForSources($this->sources);
				$this->addMoreSources($compilers);
				$this->saved_sources = array_merge($this->saved_sources, $this->sources);
				$this->compileSources($compilers_stop, $cache_dir);
			}
			$this->sources = $this->saved_sources;
			$this->text_output->log('Wave done');
		}
		foreach ($this->compilers as $compilers) {
			foreach ($compilers as $compiler) {
				if ($compiler instanceof Done_Compiler) {
					$compiler->doneCompile();
				}
			}
		}
		$this->sources = [];

		(new Dependency\Cache)->generate();

		$this->text_output->log('Compilation done');
		$this->text_output->end();
	}

	//--------------------------------------------------------------------------------- compileSource
	/**
	 * Compile one source file using compilers
	 *
	 * @param $source         Reflection_Source
	 * @param $compilers_stop integer
	 * @param $cache_dir      string
	 */
	private function compileSource(Reflection_Source $source, $compilers_stop, $cache_dir)
	{
		$source->refuseCompiledSource();
		$class_name = $source->getFirstClassName();
		foreach ($this->compilers as $compilers_position => $compilers) {
			if (isset($this->compiled[$compilers_position][$source->file_name])) {
				continue;
			}
			/** @var $compilers ICompiler[] */
			foreach ($compilers as $compiler) {
				$this->compiler = $compiler;
				if (isset($GLOBALS['D'])) {
					echo $compilers_position . ' '
						. get_class($compiler) . ' : Compile source file ' . $source->file_name
						. ' class ' . $class_name . BRLF;
				}
				$compiler->compile($source, $this);
			}
			$this->compiled[$compilers_position][$source->file_name] = true;
			if ($compilers_position === $compilers_stop) {
				break;
			}
		}
		$file_name = Files::isInPath($source->file_name, $cache_dir)
			? $source->file_name
			: ($cache_dir . SL . self::sourceFileToCacheFileName($source->file_name));
		if (isset($GLOBALS['D'])) {
			echo "size $file_name " . strlen($source->getSource()) . " changed " . $source->hasChanged()
				. BRLF;
		}
		if ($source->hasChanged()) {
			$this->has_changed[$source->file_name] = true;
			if (isset($GLOBALS['D'])) echo "script_put_contents($file_name)" . BRLF;
			script_put_contents($file_name, $source->getSource());
			$this->replaceDependencies($source);
		}
		elseif (
			$this->last_wave
			&& file_exists($file_name)
			&& !isset($this->has_changed[$source->file_name])
			&& !Class_Builder::isBuilt($source->getFirstClassName())
		) {
			if (isset($GLOBALS['D'])) {
				echo "<strong>unlink($file_name)</strong><br>";
			}
			unlink($file_name);
			$this->addMoreDependentSources($class_name);
			$this->replaceDependencies($source);
		}
	}

	//-------------------------------------------------------------------------------- compileSources
	/**
	 * @param $compilers_stop integer
	 * @param $cache_dir string
	 */
	private function compileSources($compilers_stop, $cache_dir)
	{
		$this->sortSourcesByParentsCount();
		$counter = 0;
		$total   = count($this->sources);
		foreach ($this->sources as $source) {
			$this->text_output->progress('Compiling sources......', ++$counter, $total);
			$this->compileSource($source, $compilers_stop, $cache_dir);
		}

		$this->sources      = $this->more_sources;
		$this->more_sources = [];
		foreach ($this->sources as $source_class_name => $source) {
			if (!isset($this->saved_sources[$source_class_name])) {
				$this->saved_sources[$source_class_name] = $source;
			}
		}
	}

	//------------------------------------------------------------------------------ getClassFileName
	/**
	 * Gets a Reflection_Source knowing its class _name.
	 * Use sources cache, or router's getClassFileName() and fill-in cache.
	 *
	 * @param $class_name string
	 * @return Reflection_Source
	 */
	public function getClassFileName($class_name)
	{
		if (isset($this->saved_sources[$class_name])) {
			return $this->saved_sources[$class_name];
		}
		$file_name = Class_Builder::isBuilt($class_name)
			? self::classToCacheFilePath($class_name)
			: Session::current()->plugins->get(Router::class)->getClassFileName($class_name);
		return Reflection_Source::ofFile($file_name, $class_name);
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
		Application_Updater::get()->addUpdatable($this);
	}

	//------------------------------------------------------------------------- removeOldDependencies
	/**
	 * Remove dependencies for files that where deleted.
	 * If ?Z, reset the whole dependencies storage
	 */
	private function removeOldDependencies()
	{
		$this->text_output->log('removeOldDependencies... ',false);
		Dao::createStorage(Dependency::class);
		Dao::begin();
		if ($this->full) {
			$this->text_output->log('truncate...', false);
			Dao::truncate(Dependency::class);
		}
		else {
			foreach (
				Dao::select(Dependency::class, ['file_name' => Func::distinct()]) as $file_dependency
			) {
				$file_name = $file_dependency->getValue('file_name');
				if (!file_exists($file_name)) {
					foreach (Dao::search(['file_name' => $file_name], Dependency::class) as $dependency) {
						Dao::delete($dependency);
						foreach (
							Dao::search(['dependency_name' => $dependency->class_name], Dependency::class)
							as $sub_dependency
						) {
							Dao::delete($sub_dependency);
						}
					}
				}
			}
		}
		$this->text_output->log('Done');
		Dao::commit();
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
				$dependency                  = new Dependency();
				$dependency->class_name      = $class->name;
				$dependency->dependency_name = Store_Name_Annotation::of($class)->value;
				$dependency->file_name       = $source->file_name;
				$dependency->type            = Dependency::T_STORE;
				$dependencies[]              = $dependency;
			}
		}
		(new Set)->replace(
			$dependencies, Dependency::class, ['file_name' => Func::equal($source->file_name)]
		);
	}

	//----------------------------------------------------------------- replaceDependenciesForSources
	/**
	 * Update dependencies for these source files
	 *
	 * @param $sources Reflection_Source[]
	 */
	private function replaceDependenciesForSources(array $sources)
	{
		$counter = 0;
		$total   = count($sources);
		foreach ($sources as $source) {
			$this->text_output->progress('Replace dependencies...', ++$counter, $total);
			$this->replaceDependencies($source);
		}
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
			$parents_count  = -1;
			$parent_classes = $source->getClasses();
			$parent_class   = reset($parent_classes);
			while (
				$parent_class
				&& ($parent_class instanceof Reflection_Class)
				&& !$parent_class->isInternal()
			) {
				$parents_count ++;
				$parent_name = $parent_class->getParentName();
				if ($parent_name) {
					if (isset($this->saved_sources[$parent_name])) {
						$parent_classes = $this->saved_sources[$parent_name]->getClasses();
						$parent_class   = reset($parent_classes);
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
							$parent_classes = $this->saved_sources[$file_name]->getClasses();
							$parent_class   = reset($parent_classes);
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

	//--------------------------------------------------------------------- sourceFileToCacheFileName
	/**
	 * Returns the filename where to store compiled file for given source file name
	 * 'a/class/name/like/this/This.php' or 'a/class/name/like/This.php' into
	 * 'a-class-name-like-This'>
	 *
	 * @param $file_name string
	 * @return string
	 * @see cacheFileNameToSourceFile()
	 */
	public static function sourceFileToCacheFileName($file_name)
	{
		return Include_Filter::cacheFile($file_name);
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * @param $last_time integer
	 */
	public function update($last_time = 0)
	{
		if (isset($_GET['Z']) && isset($_POST['Z'])) {
			$last_active            = Include_Filter::$active;
			Include_Filter::$active = false;
		}
		$this->compile($last_time);
		if (isset($last_active)) {
			Include_Filter::$active = $last_active;
		}
	}

}
