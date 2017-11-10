<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Reflection\Annotation\Class_\Extends_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * The class for the global application object
 *
 * The application class must be overridden by each application.
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
	 * If $flat is not set :
	 * The result key is the name of the class, the value is the list of its parents, with the same
	 * structure, recursively
	 *
	 * If $flat is set :
	 * The result key is the name of the class, values is an array of up to 2 elements :
	 * - key 'children' : the value is an array of [$child_class_name => true]
	 * - key 'parents' : the value is an array of [$parent_class_name => true]
	 *
	 * @example
	 * not $flat : ['Application\Class' => ['ITRocks\Framework' => true]]
	 * @example
	 * $flat : [
	 *   'Application\Class' => ['children' => ['ITRocks\Framework' => true]],
	 *   'ITRocks\Framework' => ['parents' => ['Application\Class]]
	 * ]
	 * @param $flat boolean if false, returned as tree. If true, returns a flat string[]
	 * @return array The classes tree
	 */
	public function getClassesTree($flat = false)
	{
		// tree
		static $tree = null;
		if (!isset($tree)) {
			$tree = [get_class($this) => static::getParentClasses(true)];
		}
		if (!$flat) {
			return $tree;
		}
		// flat
		static $flat = [];
		if ($flat) {
			return $flat;
		}
		$classes = $this->getClassTreeToArray($tree);
		do {
			$trailing_classes = [];
			foreach ($classes as $class_name => $relations) {
				$children          = isset($relations['children']) ? $relations['children'] : [];
				$all_children_done = true;
				foreach ($children as $child) {
					if (!isset($flat[$child])) {
						$all_children_done = false;
						break;
					}
				}
				if ($all_children_done) {
					$flat[$class_name] = $class_name;
				}
				else {
					$trailing_classes[$class_name] = $relations;
				}
			}
		} while ($classes = $trailing_classes);
		return $flat = array_values($flat);
	}

	//--------------------------------------------------------------------------- getClassTreeToArray
	/**
	 * @param $class_tree array
	 * @return array integer $dependencies_count[string $class_name][string $parent_class_name]
	 */
	public function getClassTreeToArray(array $class_tree, array &$result = [])
	{
		foreach ($class_tree as $class_name => $parents) {
			foreach (array_keys($parents) as $parent_class_name) {
				$result[$class_name]['parents'][$parent_class_name]  = $parent_class_name;
				$result[$parent_class_name]['children'][$class_name] = $class_name;
			}
			$this->getClassTreeToArray($parents, $result);
		}
		return $result;
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
	 * @return array[] applications class names : key = class name, value = children class names
	 */
	public static function getParentClasses($recursive = false)
	{
		$class_name          = get_called_class();
		$class               = new Reflection_Class($class_name);
		$parent_class_name   = get_parent_class($class_name);
		$extends_annotations = Extends_Annotation::allOf($class);
		$parents             = [];
		// 1st : php extends
		if ($parent_class_name) {
			$parents[$parent_class_name] = [];
		}
		// 2nd : @extends annotations
		foreach ($extends_annotations as $extends_annotation) {
			foreach ($extends_annotation->values() as $extends) {
				$parents[$extends] = [];
			}
		}
		// next : up to extended classes
		if ($recursive) {
			foreach (array_keys($parents) as $extends) {
				/** @noinspection PhpUndefinedMethodInspection sure it extends static */
				$parents[$extends] = $extends::getParentClasses(true);
			}
		}
		return $parents;
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
