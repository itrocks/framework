<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/configuration/Configuration.php";
require_once "framework/core/toolbox/Current_With_Default.php";
require_once "framework/core/toolbox/Names.php";
require_once "framework/core/toolbox/OS.php";
require_once "framework/core/toolbox/String.php";

class Application
{
	use Current { current as private pCurrent; }

	//--------------------------------------------------------------------------------- $applications
	/**
	 * Secondary super-applications if your application need modules from several parent applications
	 *
	 * @var Application[]
	 */
	public $applications = array();

	//--------------------------------------------------------------------------------- $include_path
	/**
	 * The include path for all application components
	 *
	 * @var string
	 */
	public $include_path;

	//----------------------------------------------------------------------------------- $namespaces
	/**
	 * Namespaces list cache : initialized at first use
	 *
	 * @var string[]
	 */
	public $namespaces = array();

	//-------------------------------------------------------------------------- $origin_include_path
	/**
	 * The original PHP include_path is kept here
	 *
	 * This is the base include_path when Autoloader initializes after a call to reset().
	 *
	 * @var string
	 */
	private static $origin_include_path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param array $parameters
	 */
	public function __construct($parameters = array())
	{
		if (isset($parameters["applications"])) {
			foreach ($parameters["applications"] as $application_name => $application_parameters) {
				if (is_numeric($application_name)) {
					$this->applications[$application_parameters] = new $application_parameters();
				}
				else {
					$this->applications[$application_name] = new $application_name($application_parameters);
				}
			}
		}
		if (isset($parameters["namespaces"])) {
			$this->namespaces = $parameters["namespaces"];
		}
		if (isset($parameters["include_path"])) {
			self::getOriginIncludePath();
			$this->include_path = $parameters["include_path"];
			set_include_path($parameters["include_path"]);
		}
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param Application $set_current
	 * @return Application
	 */
	public static function current(Application $set_current = null)
	{
		if (isset($set_current)) {
			set_include_path($set_current->getIncludePath());
		}
		return self::pCurrent($set_current);
	}

	//--------------------------------------------------------------------------------- getNamespaces
	/**
	 * Returns the used namespaces list for the application, including parent's applications namespaces
	 *
	 * Namespaces strings are sorted from higher-level application to basis "SAF\Framework" namespace
	 * An empty namespace will always be given first
	 *
	 * @return string[]
	 */
	public static function getCurrentNamespaces()
	{
		$current_application = self::current();
		$namespaces =& $current_application->namespaces;
		if (!$namespaces) {
			$current_configuration = Configuration::current();
			if (!$current_configuration) {
				return array(__NAMESPACE__);
			}
			else {
				$application_class = get_class($current_application);
				$namespaces = $current_application->getNamespaces();
				$current_configuration->$application_class = array("namespaces" => &$namespaces);
			}
		}
		return $namespaces;
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
			if (is_dir("$path/$entry")) {
				$directories = array_merge($directories, $this->getDirectories("$path/$entry"));
			}
		}
		return $directories;
	}

	//-------------------------------------------------------------------------------------- getFiles
	/**
	 * This is called by getSourceFiles() for recursive files reading
	 *
	 * @param $path           string base path
	 * @param $include_vendor boolean
	 * @return string[] an array of files names (empty directories are not included)
	 */
	private function getFiles($path, $include_vendor = false)
	{
		$files = array();
		$dir = dir($path);
		while ($entry = $dir->read()) if (($entry[0] != ".")) {
			if (is_file("$path/$entry")) {
				$files[] = "$path/$entry";
			}
			elseif (is_dir("$path/$entry") && ($include_vendor || ($entry != "vendor"))) {
				$files = array_merge($files, $this->getFiles("$path/$entry"));
			}
		}
		return $files;
	}

	//-------------------------------------------------------------------------------- getIncludePath
	public function getIncludePath()
	{
		if (!isset($this->include_path)) {
			$include_path = join(OS::includeSeparator(), $this->getSourceDirectories());
			$this->include_path = self::getOriginIncludePath() . OS::includeSeparator() . $include_path;
			return $this->include_path;
		}
		return $this->include_path;
	}

	//--------------------------------------------------------------------------------- getNamespaces
	/**
	 * Gets application and parents and used applications namespaces
	 *
	 * @return string[]
	 */
	public function getNamespaces()
	{
		$applications_classes = array_merge(array(get_class($this)), array_keys($this->applications));
		$already_namespaces = array();
		foreach ($applications_classes as $application_class) {
			while (
				!empty($application_class) && ($application_class != 'SAF\Framework\Application')
				&& !isset($already_namespaces[$application_class])
			) {
				$namespace = Namespaces::of($application_class);
				$namespaces[] = $namespace;
				$path = str_replace(
					"_", "", Names::classToProperty(substr($namespace, strpos($namespace, "/") + 1))
				);
				$dir = dir($path);
				while ($entry = $dir->read()) {
					if (($entry[0] != '.') && is_dir($path . "/" . $entry)) {
						$namespaces[] = $namespace . "\\" . Names::propertyToClass($entry);
					}
				}
				$dir->close();
				$already_namespaces[$application_class] = true;
				$application_class = get_parent_class($application_class);
			}
		}
		$namespaces[] = 'SAF\Framework';
		$namespaces[] = 'SAF\Framework\Unit_Tests';
		$namespaces[] = "";
		return $namespaces;
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
	 * @param $application_class string
	 * @return string[]
	 */
	public function getSourceDirectories($application_class = null)
	{
		if (!isset($application_class)) {
			$application_class = get_class($this);
		}
		$app_dir = $this->getSourceDirectory($application_class);
		$directories = array();
		if (
			($application_class != 'SAF\Framework\Application')
			&& ($application_class != '\SAF\Framework\Application')
		) {
			$extends = trim(mParse(file_get_contents("{$app_dir}/Application.php"),
				" extends ", "\n"
			));
			$directories = $this->getSourceDirectories($extends);
		}
		foreach ($this->applications as $application) {
			$directories = array_merge($directories, $this->getSourceDirectories($application));
		}
		return array_merge($this->getDirectories($app_dir), $directories);
	}

	//---------------------------------------------------------------------------- getSourceDirectory
	/**
	 * @param $application_class string
	 * @return string|null
	 */
	public function getSourceDirectory($application_class = null)
	{
		if (!isset($application_class)) {
			$application_class = get_class($this);
		}
		$namespace = Namespaces::of($application_class);
		return str_replace(
			"_", "", Names::classToProperty(substr($namespace, strrpos("\\", $namespace) + 1))
		);
	}

	//-------------------------------------------------------------------------------- getSourceFiles
	/**
	 * Returns the full files list for the application, including parent's applications directory
	 *
	 * File names are sorted from higher-level application to basis SAF "framework" directory
	 * Inside an application, files are sorted randomly (according to how the php Directory->read() call works)
	 *
	 * Paths are relative to the SAF index.php base script position
	 *
	 * @param $include_vendor   boolean
	 * @return string[]
	 */
	public function getSourceFiles($include_vendor = false)
	{
		$app_dir = $this->getSourceDirectory();
		$directories = array();
		if (get_class($this) != 'SAF\Framework\Application') {
			$extends = mParse(file_get_contents("{$app_dir}/Application.php"),
				" extends \\SAF\\", "\\Application"
			);
			$directories = $this->getSourceFiles($extends, $include_vendor);
		}
		return array_merge($this->getFiles($app_dir, $include_vendor), $directories);
	}

}
