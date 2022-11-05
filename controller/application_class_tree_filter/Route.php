<?php
namespace ITRocks\Framework\Controller\Application_Class_Tree_Filter;

/**
 * A single route is identified by :
 * - the initial crossroads child application class
 * - its first parent application class that starts the route
 *
 * It contains the list of node application classes that make it a route
 */
class Route
{

	//-------------------------------------------------------------------------------------- $checked
	/**
	 * Becomes true once any of $nodes is an official checkpoint
	 *
	 * @var boolean
	 */
	public bool $checked = false;

	//---------------------------------------------------------------------------------------- $child
	/**
	 * The route starts from a source crossroads node (child application class)
	 *
	 * @var ?Node
	 */
	public ?Node $child;

	//---------------------------------------------------------------------------------------- $first
	/**
	 * The first parent application class of $crossroads that starts the route
	 *
	 * @var ?Node
	 */
	public ?Node $first;

	//---------------------------------------------------------------------------------------- $nodes
	/**
	 * The nodes into the route
	 *
	 * @var Node[]|Route[]
	 */
	public array $nodes = [];

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * The route ends to a destination crossroads node (parent application class)
	 *
	 * @var Node
	 */
	public Node $parent;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Route constructor
	 *
	 * @param $child Node|null
	 * @param $first Node|null
	 */
	public function __construct(Node $child = null, Node $first = null)
	{
		$this->child = $child;
		$this->first = $first;
	}

	//--------------------------------------------------------------------------------------- addNode
	/**
	 * @param $node Node
	 */
	public function addNode(Node $node) : void
	{
		$this->nodes[$node->class] = $node;
		$node->route               = $this;
		if ($node->checked) {
			$this->checked = true;
		}
	}

	//-------------------------------------------------------------------------------------- addRoute
	/**
	 * @param $route Route
	 */
	public function addRoute(Route $route) : void
	{
		$this->nodes[$route->identifier()] = $route;
		if ($route->checked) {
			$this->checked = true;
		}
	}

	//------------------------------------------------------------------------------------ identifier
	/**
	 * @return string what is identifying a route as unique : '$child>$first'
	 */
	public function identifier() : string
	{
		return ($this->child ? $this->child->class : '-')
			. '>' . ($this->first ? $this->first->class : '-');
	}

}
