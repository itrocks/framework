<?php
namespace SAF\Framework;

class Acls
{

	//------------------------------------------------------------------------------------- $acl_tree
	/**
	 * $acl_tree store acls
	 *
	 * @var multitype:mixed
	 */
	private $acl_tree;

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds a right value to acls
	 *
	 * @param Acl_Right $right
	 */
	public function add(Acl_Right $right)
	{
		$this->acl_tree[$right->key] = $right->value;
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Gets a right value from acls
	 *
	 * @param string
	 * @return mixed
	 */
	public function get($key)
	{
		return $this->acl_tree[$key];
	}

}
