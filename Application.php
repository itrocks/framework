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

	//------------------------------------------------------------------------------------------ BOTH
	/**
	 * For getClassesTree : want a result with the two forms of applications lists : array, and tree
	 */
	const BOTH = null;

	//-------------------------------------------------------------------------------------- CHILDREN
	const CHILDREN = 'children';

	//------------------------------------------------------------------------------------------ FLAT
	const FLAT = 'flat';

	//----------------------------------------------------------------------------------------- NODES
	const NODES = 'nodes';

	//--------------------------------------------------------------------------------------- PARENTS
	const PARENTS = 'parents';

	//------------------------------------------------------------------------------------------ TREE
	const TREE = 'tree';

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
	 * @param $set_current Application
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
	 * @param $flat boolean|string|null if false, returned as tree. If true, returns a flat string[]
	 *              If null : multiple intermediate views are returned :
	 *              [FLAT => $applications_array, NODES => $tree_nodes, TREE => $tree]
	 *              If string : can be any const BOTH (eq null), FLAT (eq true) or TREE (eq false)
	 * @return array The classes tree : string[], tree of strings,
	 *               or [FLAT => $flat, NODES => $notes TREE => $tree]
	 */
	public function getClassesTree($flat = false)
	{
		if (is_string($flat)) {
			if ($flat === self::FLAT) {
				$flat = true;
			}
			elseif ($flat === self::TREE) {
				$flat = false;
			}
			else {
				$flat = self::BOTH;
			}
		}
		// tree
		static $tree = null;
		if (!isset($tree)) {
			$tree = [get_class($this) => static::getParentClasses(true)];
		}
		if ($flat === false) {
			return $tree;
		}
		// flat + nodes
		static $flat_cache  = [];
		static $nodes_cache = [];
		if (!$flat_cache) {
			$nodes = $this->getClassTreeToArray($tree);
			foreach ($nodes as &$class_node) {
				if (!isset($class_node[self::CHILDREN])) {
					$class_node[self::CHILDREN] = [];
				}
				if (!isset($class_node[self::PARENTS])) {
					$class_node[self::PARENTS] = [];
				}
			}
			$classes       = $nodes;
			$do_deprecated = false;
			do {
				$done             = 0;
				$trailing_classes = [];
				foreach ($classes as $class_name => $relations) {
					$children          = $relations[self::CHILDREN];
					$all_children_done = true;
					foreach ($children as $child) {
						if (!isset($nodes_cache[$child])) {
							$all_children_done = false;
							break;
						}
					}
					if (
						$all_children_done
						&& (
							$do_deprecated
							|| !(new Reflection_Class($class_name))->getAnnotation('deprecated')->value
						)
					) {
						$done ++;
						$nodes_cache[$class_name] = $relations;
					}
					else {
						$trailing_classes[$class_name] = $relations;
					}
				}
				if (!$done) {
					$do_deprecated = true;
				}
			} while ($classes = $trailing_classes);
			$flat_cache = array_keys($nodes_cache);
		}
		// return as flat, or return both forms
		return $flat
			? $flat_cache
			: [self::FLAT => $flat_cache, self::NODES => $nodes_cache, self::TREE => $tree];
	}

	//--------------------------------------------------------------------------- getClassTreeToArray
	/**
	 * @example of $class_tree input
	 * the tree structure returned by Application::getClassesTree : [
	 *   Vendor\Final_Project\Application::class => [
	 *     Vendor\Module_A\Application::class => [
	 *       ITRocks\Framework\Application::class
	 *     ],
	 *     Vendor\Module_B\Application::class => [
	 *       ITRocks\Framework\Application::class
	 *     ],
	 *     Vendor\Module_C\Application::class => [
	 *       Vendor\Sub_Module\Application::class => [
	 *         ITRocks\Framework\Application::class
	 *       ]
	 *    ]
	 * ]
	 * @param $class_tree array string[...]
	 * @param $result     array @internal The resulting classes list with links, currently being built
	 * @return array string[][][]
	 *   [
	 *     string $class_name => [
	 *       self::CHILDREN => [string $child_class_name  => string $child_class_name ],
	 *       self::PARENTS  => [string $parent_class_name => string $parent_class_name]
	 *     ]
	 *   ]
	 */
	public function getClassTreeToArray(array $class_tree, array &$result = [])
	{
		foreach ($class_tree as $class_name => $parents) {
			foreach (array_keys($parents) as $parent_class_name) {
				$result[$class_name][self::PARENTS][$parent_class_name]  = $parent_class_name;
				$result[$parent_class_name][self::CHILDREN][$class_name] = $class_name;
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
