<?php
namespace SAF\Framework;

use SAF\Framework\Reflection\Reflection_Class;

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
	public $applications = [];

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

	//--------------------------------------------------------------------------------------- $vendor
	/**
	 * @var string
	 */
	public $vendor;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name string Must look like 'Author/Application' : slash is required
	 */
	public function __construct($name)
	{
		list($this->vendor, $this->name) = explode(SL, $name);
		$this->include_path = new Include_Path(get_class($this));
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param Application $set_current
	 * @return Application
	 */
	public static function current(Application $set_current = null)
	{
		if ($set_current) {
			Session::current()->set($set_current, Application::class);
			return $set_current;
		}
		return Session::current()->get(Application::class);
	}

	//----------------------------------------------------------------------------------- getCacheDir
	/**
	 * @return string
	 */
	public function getCacheDir()
	{
		return 'cache';
	}

	//---------------------------------------------------------------------------------- getNamespace
	/**
	 * Gets namespace of the application
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		return (new Reflection_Class(get_class($this)))->getNamespaceName();
	}

	//--------------------------------------------------------------------------------- getNamespaces
	/**
	 * Gets application and parents and used applications top namespaces
	 *
	 * @return string[]
	 */
	public function getNamespaces()
	{
		if (!isset($this->namespaces)) {
			$applications_classes = array_merge([get_class($this)], array_keys($this->applications));
			$namespaces = [];
			foreach ($applications_classes as $application_class) {
				while ($application_class) {
					$namespaces[] = substr($application_class, 0, strrpos($application_class, BS));
					$application_class = get_parent_class($application_class);
				}
			}
			$this->namespaces = $namespaces;
		}
		return $this->namespaces;
	}

	//------------------------------------------------------------------------- getTemporaryFilesPath
	/**
	 * @return string
	 */
	public function getTemporaryFilesPath()
	{
		if (!is_dir('tmp')) {
			mkdir('tmp');
			file_put_contents('tmp/.htaccess', 'Deny From All');
		}
		return 'tmp';
	}

}
