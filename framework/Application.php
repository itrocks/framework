<?php
namespace SAF\Framework;

/**
 * The class for the global application object
 *
 * The application class must be overriden by each application.
 */
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

	//----------------------------------------------------------------------------------- $namespaces
	/**
	 * Namespaces list cache : initialized at first use
	 *
	 * @var string[]
	 */
	public $namespaces = array();

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
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param Application $set_current
	 * @return Application
	 */
	public static function current(Application $set_current = null)
	{
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

	//-------------------------------------------------------------------------------- getSourceFiles
	/**
	 * Returns the full files list for the application, including parent's applications directory
	 *
	 * File names are sorted from higher-level application to basis SAF "framework" directory
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
		foreach ((new Include_Path())->getSourceDirectories() as $directory) {
			$directory_slash = $directory . "/";
			if (
				(strpos($directory_slash, "/webshop/templates/") === false)
				&& ($include_vendor || strpos($directory_slash, "/vendor/") === false)
			) {
				$dir = dir($directory);
				while ($entry = $dir->read()) if ($entry[0] != ".") {
					$file_path = $directory . "/" . $entry;
					if (is_file($file_path)) {
						$files[] = $file_path;
					}
				}
				$dir->close();
			}
		}
		return $files;
	}

	//------------------------------------------------------------------------- getTemporaryFilesPath
	/**
	 * @return string
	 */
	public function getTemporaryFilesPath()
	{
		if (!is_dir("tmp")) {
			mkdir("tmp");
			file_put_contents("tmp/.htaccess", "Deny From All");
		}
		return "tmp";
	}

}
