<?php
namespace ITRocks\Framework\Controller\Application_Class_Tree_Filter;

/**
 * A node is an application class, with links to its children and parents
 */
class Node
{

	//-------------------------------------------------------------------------------------- $checked
	/**
	 * @var boolean true if the node is a checkpoint
	 */
	public $checked = false;

	//------------------------------------------------------------------------------------- $children
	/**
	 * @var Node[]
	 */
	public $children = [];

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var string
	 */
	public $class;

	//-------------------------------------------------------------------------------- $closed_routes
	/**
	 * @var Route[] The routes closed by this node
	 */
	public $closed_routes = [];

	//-------------------------------------------------------------------------------------- $parents
	/**
	 * @var Node[]
	 */
	public $parents = [];

	//---------------------------------------------------------------------------------------- $route
	/**
	 * @var Route The route where the node is in
	 */
	public $route;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Node constructor
	 *
	 * @param $class string
	 */
	public function __construct($class)
	{
		$this->class = $class;
	}

	//------------------------------------------------------------------------------- allRoutesClosed
	/**
	 * @return boolean true if all routes were closed
	 */
	public function allRoutesClosed()
	{
		return count($this->children) === count($this->closed_routes);
	}

	//---------------------------------------------------------------------------------------- closes
	/**
	 * @return boolean true if the node closes multiple routes (closing crossroads)
	 */
	public function closes()
	{
		return count($this->children) > 1;
	}

	//------------------------------------------------------------------------ hasCheckedClosedRoutes
	/**
	 * @return boolean true if at least one closed route is checked
	 */
	public function hasCheckedClosedRoutes()
	{
		foreach ($this->closed_routes as $closed_route) {
			if ($closed_route->checked) {
				return true;
			}
		}
		return false;
	}

	//----------------------------------------------------------------------------------------- opens
	/**
	 * @return boolean true if the node opens multiple routes (initial crossroads)
	 */
	public function opens()
	{
		return count($this->parents) > 1;
	}

}
