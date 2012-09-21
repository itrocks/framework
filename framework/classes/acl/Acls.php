<?php
namespace SAF\Framework;

class Acls
{

	//------------------------------------------------------------------------------------- $acl_tree
	/**
	 * $acl_tree replaces access to access objects : it's a multi-indices table which indices are
	 * $class, $method, $right and value is the effective right's value
	 *
	 * @var array
	 */
	private $acl_tree;

}
