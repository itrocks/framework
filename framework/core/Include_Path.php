<?php
namespace SAF\Framework;

/**
 * Include path manager
 */
class Include_Path
{

	//---------------------------------------------------------------------------------- $application
	/**
	 * @var string
	 */
	private $application;

	//-------------------------------------------------------------------------- $origin_include_path
	/**
	 * The original PHP include_path is kept here
	 *
	 * @var string
	 */
	private static $origin_include_path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $application string
	 */
	public function __construct($application = 'framework')
	{
		$this->application = $application;
	}

	//-------------------------------------------------------------------------------- getDirectories
	/**
	 * This is called by getSourceDirectories() for recursive directories reading
	 *
	 * @param $path string base path
	 * @return string[] an array of directories names
	 */
	private function getDirectories($path)
	{
		$directories = array($path);
		$dir = dir($path);
		while ($entry = $dir->read()) if ($entry[0] != '.') {
			if (is_dir($path . '/' . $entry) && ($entry != 'vendor')) {
				$directories = array_merge($directories, $this->getDirectories($path . '/' . $entry));
			}
		}
		return $directories;
	}

	//-------------------------------------------------------------------------------- getIncludePath
	/**
	 * @return string
	 */
	public function getIncludePath()
	{
		if (!isset($this->include_path)) {
			$include_path = join(OS::$include_separator, $this->getSourceDirectories(true));
			$this->include_path = self::getOriginIncludePath() . OS::$include_separator . $include_path;
			return $this->include_path;
		}
		return $this->include_path;
	}

	//-------------------------------------------------------------------------- getOriginIncludePath
	/**
	 * Returns PHP origin include path
	 *
	 * @return string
	 */
	public static function getOriginIncludePath()
	{
		if (!self::$origin_include_path) {
			self::$origin_include_path = get_include_path();
		}
		return self::$origin_include_path;
	}

	//-------------------------------------------------------------------------- getSourceDirectories
	/**
	 * Returns the full directory list for the application, including parent's applications directory
	 *
	 * Directory names are sorted from higher-level application to basis SAF 'framework' directory
	 * Inside an application, directories are sorted randomly (according to how the php
	 * Directory->read() call works)
	 *
	 * Paths are relative to the SAF index.php base script position
	 *
	 * @param $include_subdirectories boolean
	 * @param $application            string
	 * @return string[]
	 */
	public function getSourceDirectories($include_subdirectories = false, $application = null)
	{
		if (!isset($application)) {
			$application = $this->application;
		}
		$app_dir = $this->getSourceDirectory($application);
		$directories = array();
		if ($application != 'framework') {
			$extends = trim(mParse(file_get_contents($app_dir . '/Application.php'), ' extends ', "\n"));
			$extends = substr($extends, 0, strrpos($extends, '\\'));
			$extends = substr($extends, strrpos($extends, '\\') + 1);
			if ($extends) {
				$directories = $this->getSourceDirectories($include_subdirectories, strtolower($extends));
			}
		}
		/*
		// todo multiple applications extends management
		foreach ($this->applications as $application) {
			$directories += $this->getSourceDirectories($application);
		}
		*/
		return $include_subdirectories
			? array_merge($this->getDirectories($app_dir), $directories)
			: array_merge(array($application), $directories);
	}

	//---------------------------------------------------------------------------- getSourceDirectory
	/**
	 * @param $application string
	 * @return string
	 */
	public function getSourceDirectory($application = null)
	{
		return isset($application) ? $application : $this->application;
	}

	//-------------------------------------------------------------------------------- getSourceFiles
	/**
	 * Returns the full files list for the application, including parent's applications directory
	 *
	 * File names are sorted from higher-level application to basis SAF 'framework' directory
	 * Inside an application, files are sorted randomly (according to how the php Directory->read() call works)
	 *
	 * Paths are relative to the SAF index.php base script position
	 *
	 * @param $include_vendor boolean
	 * @return string[]
	 */
	public function getSourceFiles($include_vendor = false)
	{
		$files = array();
		foreach ($this->getSourceDirectories(true) as $directory) {
			$directory_slash = $directory . '/';
			if (
				(strpos($directory_slash, '/webshop/templates/') === false)
				&& ($include_vendor || strpos($directory_slash, '/vendor/') === false)
			) {
				$dir = dir($directory);
				while ($entry = $dir->read()) if ($entry[0] != '.') {
					$file_path = $directory . '/' . $entry;
					if (is_file($file_path)) {
						$files[] = $file_path;
					}
				}
				$dir->close();
			}
		}
		return $files;
	}

}
