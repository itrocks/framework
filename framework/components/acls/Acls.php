<?php
namespace SAF\Framework;

class Acls
{
	use Current { current as private pCurrent; }

	//------------------------------------------------------------------------------------- $acl_tree
	/**
	 * $acl_tree store acls into a recursive tree
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
		$path = explode(".", $right->key);
		$position = &$this->acl_tree;
		foreach ($path as $step) {
			if (!isset($position[$step])) {
				$position[$step] = array();
			}
			$position = &$position[$step];
		}
		$position = $right->value;
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param Acls $set_current
	 * @return Acls
	 */
	public static function current(Acls $set_current = null)
	{
		return self::pCurrent($set_current);
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Gets a right value from acls
	 *
	 * @param string right key : a "key.subkey.another" path
	 * @return mixed right value
	 */
	public function get($key)
	{
		$path = explode(".", $key);
		$position = $this->acl_tree;
		if ($key) {
			foreach ($path as $step) {
				if (!isset($position[$step])) {
					return null;
				}
				$position = $position[$step];
			}
		}
		return $position;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove a right value from acls
	 *
	 * @param string right key : a "key.subkey.another" path
	 */
	public function remove(Acl_Right $right)
	{
		$path = explode(".", $right->key);
		$position = &$this->acl_tree;
		$last = null;
		foreach ($path as $step) {
			if(!isset($position[$step]))
				return;
			$last = &$position;
			$position = &$position[$step];
		}
		unset($last[$position]);
	}

}
