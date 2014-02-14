<?php
namespace SAF\Framework;

/**
 * The class for the global application object
 *
 * The application class must be overriden by each application.
 */
class Application
{

	//--------------------------------------------------------------------------------- $applications
	/**
	 * Secondary super-applications if your application need modules from several parent applications
	 *
	 * @var Application[]
	 */
	public $applications = array();

	//--------------------------------------------------------------------------------- $include_path
	/**
	 * Paths functions relative to the application
	 *
	 * @var Include_Path
	 */
	public $include_path;

	//----------------------------------------------------------------------------------- $namespaces
	/**
	 * Namespaces list cache : initialized at first use
	 *
	 * @var string[]
	 */
	private $namespaces;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name string
	 */
	public function __construct($name)
	{
		$this->name = $name;
		$this->include_path = new Include_Path($name);
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param Application $set_current
	 * @return Application
	 */
	public static function current(Application $set_current = null)
	{
		if ($set_current) {
			Session::current()->set($set_current, 'SAF\Framework\Application');
			return $set_current;
		}
		return Session::current()->get('SAF\Framework\Application');
	}

	//--------------------------------------------------------------------------------- getNamespaces
	/**
	 * Gets application and parents and used applications namespaces
	 *
	 * @return string[]
	 */
	public function getNamespaces()
	{
		if (isset($this->namespaces)) {
			return $this->namespaces;
		}
		else {
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
			$this->namespaces = $namespaces;
			return $namespaces;
		}
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
