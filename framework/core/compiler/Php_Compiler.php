<?php
namespace SAF\Framework;

use SAF\Plugins;

/**
 * Php compiler : the php scripts compilers manager
 */
class Php_Compiler implements Plugins\Registerable, Updatable
{

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $last_time integer compile only files modified since this time
	 */
	public function compile($last_time = 0)
	{
		echo '<pre>' . print_r($this->getFilesToCompile($last_time), true) . '</pre>';
	}

	//----------------------------------------------------------------------------- getFilesToCompile
	/**
	 * @param $last_time integer scan only files modified since this time
	 * @return string[] key is full file path, value is file name
	 */
	private function getFilesToCompile($last_time = 0)
	{
		$source_files = Application::current()->include_path->getSourceFiles();
		foreach (scandir('.') as $file_name) {
			if (substr($file_name, -4) == '.php') {
				$source_files[] = $file_name;
			}
		}
		$files = [];
		foreach ($source_files as $file_path) {
			if (filemtime($file_path) > $last_time) {
				$pos = strrpos($file_path, '/');
				$files[$file_path] = $pos ? substr($file_path, $pos + 1) : $file_path;
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

	//---------------------------------------------------------------------------------------- update
	/**
	 * @param $last_time integer
	 */
	public function update($last_time = 0)
	{
		$this->compile($last_time);
	}

}
