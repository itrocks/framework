<?php
namespace SAF\Framework;

use SAF\Plugins;
use Serializable;

/**
 * Php compiler : the php scripts compilers manager
 */
class Php_Compiler implements Plugins\Configurable, Plugins\Registerable, Serializable, Updatable
{

	//------------------------------------------------------------------------------------ $cache_dir
	/**
	 * @var string
	 */
	public $cache_dir;

	/**
	 * @var ICompiler[]
	 */
	public $compilers = [];

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

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $last_time integer compile only files modified since this time
	 */
	public function compile($last_time = 0)
	{
		$this->compileFiles($this->getFilesToCompile($last_time));
	}

	//---------------------------------------------------------------------------------- compileFiles
	/**
	 * @param $files string[]
	 */
	public function compileFiles($files)
	{
		$cache_dir = Application::current()->getCacheDir() . '/compiled';
		Files::mkdir($cache_dir);

		// get source and update dependencies
		foreach ($files as $file_path) {
			$source = new Php_Source($file_path);
			/** @noinspection PhpParamsInspection inspector bug (a Dependency is an object) */
			(new Dao_Set())->replace(
				$source->getDependencies(true),
				Dependency::class, ['file_name' => $source->getFileName()]
			);
			$files[$file_path] = $source;
		}

		/** @var $files Php_Source[] Key is file path */
		// ask each compiler for adding of compiled files, until they have nothing to add
		do {
			$added = false;
			foreach ($this->compilers as $compiler) {
				if ($compiler->moreFilesToCompile($files)) {
					$added = true;
				}
			}
			if (count($this->compilers) == 1) {
				$added = false;
			}
		} while ($added);

		// compile files
		foreach ($files as $key => $source) {
			unset($files[$key]);
			$compiled = false;
			foreach ($this->compilers as $compiler) {
				if ($compiler->compile($source)) {
					$compiled = true;
				}
			}
			$file_name = $cache_dir . SL . str_replace(SL, '-', substr($source->getFileName(), 0, -4));
			if ($compiled) {
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
	}

	//----------------------------------------------------------------------------- getFilesToCompile
	/**
	 * Gets the list of php files that were modifier since $last_time
	 *
	 * @param $last_time integer scan only files modified since this time
	 * @return string[] key is full file path, value is file name
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
				$files[$file_path] = $file_path;
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
