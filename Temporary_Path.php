<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Dao\File;

/**
 * All methods related to the Application's temporary path management
 *
 * @extends Application
 */
trait Temporary_Path
{

	//----------------------------------------------------------------------- buildTemporaryFilesPath
	/**
	 * Build the path of temporary files folder
	 *
	 * @return string
	 */
	protected function buildTemporaryFilesPath()
	{
		// one temporary files path per user, in order to avoid conflicts bw www-data and other users
		// - user is www-data : /home/tmp/helloworld (no 'www-data' in this case)
		// - user is root : /home/tmp/helloworld.root
		$user = function_exists('posix_getuid') ? posix_getpwuid(posix_getuid())['name'] : 'www-data';
		$files_link = Dao::get('tmp-files');
		if (!($files_link instanceof File\Link)) {
			$files_link = Dao::get('files');
		}
		$root = ($files_link instanceof File\Link) ? $files_link->getPath() : '/home/';
		return ($root . 'tmp/'
			. str_replace(SL, '-', strUri($this->name))
			. (($user === 'www-data') ? '' : (DOT . $user)));
	}

	//-------------------------------------------------------------------- createTemporaryFilesFolder
	/**
	 * Create the folder for temporary files.
	 * Folder is supposed not to exist.
	 *
	 * @param $path string
	 */
	protected function createTemporaryFilesFolder($path)
	{
		// 2 concurrent processes may create the folder at same time
		// If the folder creation failed
		/** @noinspection PhpUsageOfSilenceOperatorInspection */
		if (@mkdir($path, 0777, true) !== true) {
			// Check if it exists, finally. If not then this is really an error
			clearstatcache(true, $path);
			if (!is_dir($path)) {
				$error = error_get_last();
				trigger_error($path . ' : ' . $error['message'], E_USER_ERROR);
			}
		}
		// Init the folder only if has been created by this process
		else {
			$this->initTemporaryFilesFolder($path);
		}
	}

	//------------------------------------------------------------------------- getTemporaryFilesPath
	/**
	 * Get the temporary files path folder and create this later if it does not exist
	 *
	 * @return string
	 */
	public function getTemporaryFilesPath()
	{
		if (!Session::current()->temporary_directory) {
			Session::current()->temporary_directory = $this->buildTemporaryFilesPath();
		}

		$path = Session::current()->temporary_directory;
		if (!is_dir($path)) {
			$this->createTemporaryFilesFolder($path);
		}

		return $path;
	}

	//---------------------------------------------------------------------- initTemporaryFilesFolder
	/**
	 * Initialize the folder of temporary files.
	 * Folder is supposed to be empty (freshly created).
	 *
	 * @param $path string
	 */
	protected function initTemporaryFilesFolder($path)
	{
		// in case of this directory is publicly accessible into an Apache2 website
		file_put_contents($path . '/.htaccess', 'Deny From All');
	}

}
