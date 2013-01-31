<?php
namespace SAF\Framework;

class Acls_Rights
{
	use Current { current as private pCurrent; }

	//------------------------------------------------------------------------------------ $acls_tree
	/**
	 * stores acls into a recursive tree
	 *
	 * @var mixed[]
	 */
	private $acls_tree;

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds a right value to acls rights
	 *
	 * @param $right Acls_Right
	 */
	public function add(Acls_Right $right)
	{
		$path = explode(".", $right->key);
		$position = &$this->acls_tree;
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
	 * @param $set_current Acls_Rights
	 * @return Acls_Rights
	 */
	public static function current(Acls_Rights $set_current = null)
	{
		return self::pCurrent($set_current);
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Gets a right value from acls rights
	 *
	 * @param $key string right key : a "key.subkey.another" path
	 * @return mixed right value
	 */
	public function get($key)
	{
		$path = explode(".", $key);
		$position = $this->acls_tree;
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
	 * Remove a right value from acls rights
	 *
	 * @param Acls_Right|string right key : a "key.subkey.another" path
	 */
	public function remove($right)
	{
		$position =& $this->acls_tree;
		$last_position = null;
		foreach (explode(".", (is_string($right) ? $right : $right->key)) as $right) {
			if (!isset($position[$right])) {
				return;
			}
			$last_position =& $position;
			$position =& $position[$right];
		}
		if (isset($last_position) && isset($last_position[$right])) {
			unset($last_position[$right]);
		}
	}

}
