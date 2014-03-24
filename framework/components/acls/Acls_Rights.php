<?php
namespace SAF\Framework;

/**
 * Acls_Rights manages a rights tree
 */
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
	 * @param $right Acls_Right|string
	 * @param $value string
	 */
	public function add($right, $value = null)
	{
		if ($right instanceof Acls_Right) {
			$key = $right->key;
			if (!isset($value)) {
				$value = $right->value;
			}
		}
		else {
			$key = $right;
			if (!isset($value)) {
				$value = true;
			}
		}
		$position = &$this->acls_tree;
		$step = '';
		foreach (explode(DOT, $key) as $step) {
			if (isset($position) && !is_array($position)) {
				$position = ['=' => $position];
			}
			if (!isset($position[$step])) {
				$position[$step] = [];
			}
			$last_position = &$position;
			$position = &$position[$step];
		}
		$last_position[$step] = $value;
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
	 * @param $key string right key : a 'key.subkey.another' path
	 * @return string|array right value, may be an array if key is not a final node
	 */
	public function get($key)
	{
		$position = $this->acls_tree;
		if ($key) {
			foreach (explode(DOT, $key) as $step) {
				if (!isset($position[$step])) {
					return null;
				}
				$position = $position[$step];
			}
		}
		if (is_array($position)) {
			$position = treeToArray($position, '=');
		}
		return $position;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove a right value from acls rights
	 *
	 * @param Acls_Right|string right key : a 'key.subkey.another' path
	 */
	public function remove($right)
	{
		$key = is_string($right) ? $right : $right->key;
		$position =& $this->acls_tree;
		$last_position = null;
		$step = '';
		foreach (explode(DOT, $key) as $step) {
			if (!isset($position[$step])) {
				return;
			}
			$last_position =& $position;
			$position =& $position[$step];
		}
		if (isset($last_position) && isset($last_position[$step])) {
			unset($last_position[$step]);
		}
	}

}
