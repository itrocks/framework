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
	public function __construct($application = "framework")
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
		while ($entry = $dir->read()) if ($entry[0] != ".") {
			if (is_dir($path . "/" . $entry) && ($entry != "vendor")) {
				$directories = array_merge($directories, $this->getDirectories($path . "/" . $entry));
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
			$include_path = join(OS::$include_separator, $this->getSourceDirectories());
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
	 * Directory names are sorted from higher-level application to basis SAF "framework" directory
	 * Inside an application, directories are sorted randomly (according to how the php Directory->read() call works)
	 *
	 * Paths are relative to the SAF index.php base script position
	 *
	 * @param $application string
	 * @return string[]
	 */
	public function getSourceDirectories($application = null)
	{
		if (!isset($application)) {
			$application = $this->application;
		}
		$app_dir = $this->getSourceDirectory($application);
		$directories = array();
		if ($application != "framework") {
			$extends = trim(mParse(file_get_contents($app_dir . "/Application.php"), " extends ", "\n"));
			$extends = substr($extends, 0, strrpos($extends, "\\"));
			$extends = substr($extends, strrpos($extends, "\\") + 1);
			if ($extends) {
				$directories = $this->getSourceDirectories(strtolower($extends));
			}
		}
		/*
		// todo multiple applications extends management
		foreach ($this->applications as $application) {
			$directories += $this->getSourceDirectories($application);
		}
		*/
		return array_merge($this->getDirectories($app_dir), $directories);
	}

	//---------------------------------------------------------------------------- getSourceDirectory
	/**
	 * @param $application string
	 * @return string
	 */
	public function getSourceDirectory($application = null)
	{
		if (!isset($application)) {
			$application = $this->application;
		}
		return $application;
	}

}
