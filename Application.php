<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Reflection\Reflection_Class;

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

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------- $namespaces
	/**
	 * Namespaces list cache : initialized at first use
	 *
	 * @var string[]
	 */
	private $namespaces;

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
		$session = Session::current();
		if ($session == null) {
			$session = new Session();
		}
		/** @var $application Application */
		$application = $session->get(Application::class);
		return $application;
	}

	//----------------------------------------------------------------------------------- getCacheDir
	/**
	 * @return string
	 */
	public static function getCacheDir()
	{
		return __DIR__ . '/../../cache';
	}

	//-------------------------------------------------------------------------------- getClassesTree
	/**
	 * Get application class name, and all its parent applications class names
	 * Include extended parents using T_EXTENDS clause or @extends annotation
	 *
	 * @return string[]
	 */
	public function getClassesTree()
	{
		return array_merge([get_class($this)], static::getParentClasses(true));
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

	//------------------------------------------------------------------------------ getParentClasses
	/**
	 * Gets application parent classes names
	 * Include extended parents using T_EXTENDS clause or @extends annotation
	 *
	 * @param $recursive boolean get all parents if true
	 * @return string[] applications class names
	 */
	public static function getParentClasses($recursive = false)
	{
		$class_name = get_called_class();
		$class = new Reflection_Class($class_name);
		$parent_class_name   = get_parent_class($class_name);
		$extends_annotations = $class->getListAnnotations('extends');
		$extends_class_names = [];
		foreach ($extends_annotations as $extends_annotation) {
			$extends_class_names = array_merge($extends_class_names, $extends_annotation->values());
		}
		$parents = $parent_class_name
			? array_merge([$parent_class_name], $extends_class_names)
			: $extends_class_names;
		if ($recursive) {
			foreach ($parents as $parent_class) if ($parent_class) {
				$parents = array_merge($parents, call_user_func([$parent_class, 'getParentClasses'], true));
			}
		}
		return array_unique($parents);
	}

	//------------------------------------------------------------------------- getTemporaryFilesPath
	/**
	 * @return string
	 */
	public function getTemporaryFilesPath()
	{
		if (!Session::current()->temporary_directory) {
			// one temporary files path per user, in order to avoid conflicts bw www-data and other users
			// - user is www-data : /tmp/helloworld (no 'www-data' in this case)
			// - user is root : /tmp/helloworld.root
			$user = function_exists('posix_getuid') ? posix_getpwuid(posix_getuid())['name'] : 'www-data';
			Session::current()->temporary_directory = '/tmp/' . str_replace(SL, '-', strUri($this->name))
				. (($user === 'www-data') ? '' : (DOT . $user));
		}

		$path = Session::current()->temporary_directory;

		if (!is_dir($path)) {
			mkdir($path);
			// in case of this directory is publicly accessible into an Apache2 website
			file_put_contents($path . '/.htaccess', 'Deny From All');
		}

		return $path;
	}

}
