<?php
namespace ITRocks\Framework\Controller;

use ITRocks\Framework\Application;
use ITRocks\Framework\Controller\Application_Class_Tree_Filter\Node;
use ITRocks\Framework\Controller\Application_Class_Tree_Filter\Route;

/**
 * Application class tree filter
 */
class Application_Class_Tree_Filter
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * The base class name to use to build the route used for the filter
	 *
	 * @var string
	 */
	protected $class_name;

	//---------------------------------------------------------------------------------------- $nodes
	/**
	 * The application classes hierarchy flatten with nodes, and ordered
	 *
	 * @var Node[] key is the application class name
	 */
	protected $nodes = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 */
	public function __construct($class_name)
	{
		$this->class_name = $class_name;
	}

	//--------------------------------------------------------------------------------------- classes
	/**
	 * @return string[] Application classes prepared by prepare() that were not filtered by filter()
	 */
	public function classes()
	{
		return array_keys($this->nodes);
	}

	//------------------------------------------------------------------------------------ closeRoute
	/**
	 * @param $route Route
	 * @param $node  Node
	 */
	protected function closeRoute(Route $route, Node $node)
	{
		$route->parent         = $node;
		$node->closed_routes[] = $route;
		if ($node->allRoutesClosed()) {
			if ($node->hasCheckedClosedRoutes()) {
				$this->removeUncheckedRouteNodes($node->closed_routes);
			}
			if ($route->child) {
				$route = $route->child->route;
				$route->addNode($node);
			}
			if ($node->parents) {
				$this->follow($route, reset($node->parents));
			}
		}
	}

	//--------------------------------------------------------------------- defaultApplicationClasses
	/**
	 * @return string[] Application classes from classes() where we look only for defaults
	 */
	public function defaultApplicationClasses()
	{
		$class_name = $this->class_name;
		if (class_exists($class_name)) {
			while (get_parent_class($class_name)) {
				$class_name = get_parent_class($class_name);
			}
		}

		$default_application_classes    = [];
		$do_default_application_classes = false;
		foreach ($this->classes() as $application_class_name) {
			if (beginsWith($class_name, lLastParse($application_class_name, BS . 'Application') . BS)) {
				$do_default_application_classes = true;
			}
			elseif ($do_default_application_classes) {
				$default_application_classes[$application_class_name] = $application_class_name;
			}
		}

		return $default_application_classes;
	}

	//---------------------------------------------------------------------------------------- filter
	/**
	 * Filter application classes to keep only routes that go through checkpoints,
	 * or all routes if no route has a checkpoint
	 *
	 * @return static
	 */
	public function filter()
	{
		$node  = reset($this->nodes);
		$route = new Route(null, $node);
		$this->follow($route, $node);
		return $this;
	}

	//---------------------------------------------------------------------------------------- follow
	/**
	 * Follow the route $route : will add $node or close it ?
	 *
	 * @param $route Route
	 * @param $node  Node
	 */
	protected function follow(Route $route, Node $node)
	{
		if ($node->closes()) {
			$this->closeRoute($route, $node);
		}
		else {
			$route->addNode($node);
		}
		if ($node->opens()) {
			$this->openRoutes($node);
		}
		elseif ($node->parents && !$node->closes()) {
			$this->follow($route, reset($node->parents));
		}
	}

	//------------------------------------------------------------------------------------ openRoutes
	/**
	 * @param $node Node
	 */
	protected function openRoutes(Node $node)
	{
		foreach ($node->parents as $parent) {
			$route = new Route($node, $parent);
			$node->route->addRoute($route);
			$this->follow($route, $parent);
		}
	}

	//--------------------------------------------------------------------------------------- prepare
	/**
	 * @return static
	 */
	public function prepare()
	{
		$this->prepareNodes();
		$this->prepareCheckpoints();
		return $this;
	}

	//---------------------------------------------------------------------------- prepareCheckpoints
	/**
	 * Mark all checkpoint nodes
	 */
	protected function prepareCheckpoints()
	{
		$class_name = $this->class_name;
		do {
			// normal case : Vendor\Project\Application
			$application_class = lParse($class_name, BS, 2) . BS . 'Application';
			// special case : Vendor\Application (core projects)
			if (!isset($this->nodes[$application_class])) {
				$application_class = lParse($application_class, BS) . BS . 'Application';
			}
			if (isset($this->nodes[$application_class])) {
				$this->nodes[$application_class]->checked = true;
			}
		}
		while (class_exists($class_name) && ($class_name = get_parent_class($class_name)));
	}

	//---------------------------------------------------------------------------------- prepareNodes
	/**
	 * Prepare the nodes list
	 */
	protected function prepareNodes()
	{
		$applications = Application::current()->getClassesTree(Application::BOTH);

		foreach ($applications[Application::NODES] as $class => $node) {
			$this->nodes[$class] = new Node($class);
		}

		foreach ($applications[Application::NODES] as $class => $node) {
			foreach ($node[Application::CHILDREN] as $child) {
				$this->nodes[$class]->children[$child] = $this->nodes[$child];
			}
			foreach ($node[Application::PARENTS] as $parent) {
				$this->nodes[$class]->parents[$parent] = $this->nodes[$parent];
			}
		}
	}

	//--------------------------------------------------------------------- removeUncheckedRouteNodes
	/**
	 * Remove nodes that are into unchecked routes into $routes
	 *
	 * @param $routes Route[]
	 */
	private function removeUncheckedRouteNodes(array $routes)
	{
		foreach ($routes as $route) {
			if (!$route->checked) {
				foreach ($route->nodes as $node) {
					if ($node instanceof Node) {
						unset($this->nodes[$node->class]);
					}
				}
			}
		}
	}

}
