<?php
namespace SAF\Framework;

use SAF\Plugins;
use Serializable;

/**
 * Php compiler : the php scripts compilers manager
 */
class Php_Compiler
	implements Needs_Main_Controller, Plugins\Configurable, Plugins\Registerable, Serializable,
		Updatable
{

	//------------------------------------------------------------------------------------ $cache_dir
	/**
	 * Cache directory name
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

	//------------------------------------------------------------------------------------- $compiler
	/**
	 * The list of compilers used successively on PHP sources
	 *
	 * @var ICompiler[]
	 */
	private $compilers = [];

	//-------------------------------------------------------------------------------------- $sources
	/**
	 * List of PHP sources being compiled.
	 *
	 * @var Php_Source[]
	 */
	private $sources;

	//------------------------------------------------------------------------------ $main_controller
	/**
	 * The main controller that called the compilation process
	 *
	 * @var Main_Controller
	 */
	public $main_controller;

	//--------------------------------------------------------------------------------- $more_sources
	/**
	 * List of PHP sources to be compiled on next wave
	 *
	 * @var Php_Source[]
	 */
	private $more_sources = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration string[]
	 */
	public function __construct($configuration = null)
	{
		if (isset($configuration)) {
			foreach ($configuration as $class_name) {
				$this->compilers[] = Session::current()->plugins->get($class_name);
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

	//--------------------------------------------------------------------------------- addMoreSource
	/**
	 * Adds a PHP source to the sources to be compiled
	 * This can be called before or during the compilation process, so these new files will be
	 * compiled at the next compilation wave, when all current listed sources are compiled.
	 *
	 * @param $source Php_Source
	 */
	public function addSource(Php_Source $source)
	{
		/** @var Php_Source[] $new_sources_to_compile */
		if (!isset($source->file_name)) {
			$classes = $source->getClasses();
			if ($classes) {
				$class = reset($classes);
				$source->file_name
					= $this->cache_dir . SL . strtolower(str_replace(BS, '-', $class->name));
			}
			else {
				trigger_error(
					'You should only compile php scripts containing a class'
					. ' (' . $source->file_name . ' compiled with ' . get_class($this->compiler) . ')',
					E_USER_ERROR
				);
			}
		}
		$this->more_sources[$source->file_name] = $source;
	}

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $last_time integer compile only files modified since this time
	 */
	public function compile($last_time = 0)
	{
		$this->cache_dir = Application::current()->getCacheDir() . '/compiled';
		Files::mkdir($this->cache_dir);

		$this->sources = array_merge($this->more_sources, $this->getFilesToCompile($last_time));
		while ($this->sources) {

			// get source and update dependencies
			foreach ($this->sources as $source) {
				/** @var Php_Source $source inspector bug */
				/** @noinspection PhpParamsInspection inspector bug (a Dependency is an object) */
				(new Dao_Set())->replace(
					$source->getDependencies(true),
					Dependency::class, ['file_name' => $source->file_name]
				);
			}

			// ask each compiler for adding of compiled files, until they have nothing to add
			do {
				$added = false;
				foreach ($this->compilers as $compiler) {
					if ($compiler instanceof Needs_Main_Controller) {
						$compiler->setMainController($this->main_controller);
					}
					if ($compiler->moreSourcesToCompile($this->sources)) {
						$added = true;
					}
				}
				if (count($this->compilers) == 1) {
					$added = false;
				}
			} while ($added);

			// compile sources
			foreach ($this->sources as $source) {
				foreach ($this->compilers as $compiler) {
					$compiler->compile($source, $this);
				}
				$file_name = $this->cache_dir . SL
					. str_replace(SL, '-', substr($source->file_name, 0, -4));
				if ($source->changed) {
					echo '- Comp. save into ' . $file_name . '<br>';
					script_put_contents($file_name, $source->getSource());
				}
				else {
					if (is_file($file_name)) {
						echo '- Comp. remove ' . $file_name . '<br>';
						unlink($file_name);
					}
				}
			}

			$this->sources = $this->more_sources;
			$this->more_sources = [];
		}
		$this->sources = [];

	}

	//----------------------------------------------------------------------------- getFilesToCompile
	/**
	 * Gets the list of php files that were modifier since $last_time
	 *
	 * @param $last_time integer scan only files modified since this time
	 * @return Php_Source[] key is full file path, value is file name
	 */
	private function getFilesToCompile($last_time = 0)
	{
		$source_files = Application::current()->include_path->getSourceFiles();
		foreach (scandir('.') as $file_name) {
			$source_files[] = $file_name;
		}
		$files = [];
		foreach ($source_files as $file_path) {
			if ((substr($file_path, -4) == '.php') && (filemtime($file_path) > $last_time)) {
				$files[$file_path] = new Php_Source($file_path);
			}
		}
		return $files;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
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
		$compilers = [];
		foreach ($this->compilers as $compiler) {
			$compilers[] = is_object($compiler) ? get_class($compiler) : $compiler;
		}
		return serialize($compilers);
	}

	//----------------------------------------------------------------------------- setMainController
	/**
	 * @param $main_controller Main_Controller
	 */
	public function setMainController(Main_Controller $main_controller)
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
		foreach (unserialize($serialized) as $class_name) {
			$this->compilers[] = Session::current()->plugins->get($class_name);
		}
	}

}
