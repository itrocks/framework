<?php
namespace SAF\Framework;

/**
 * An acls right
 *
 * @representative group, key
 */
class Acls_Right
{
	use Component;

	//---------------------------------------------------------------------------------------- $group
	/**
	 * @composite
	 * @link Object
	 * @var Acls_Group
	 */
	public $group;

	//------------------------------------------------------------------------------------------ $key
	/**
	 * @var string
	 */
	public $key;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	public $value = true;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $group Acls_Group
	 * @param $key   string
	 * @param $value string
	 */
	public function __construct(Acls_Group $group = null, $key = null, $value = null)
	{
		if (isset($group)) $this->group = $group;
		if (isset($key))   $this->key   = $key;
		if (isset($value)) $this->value = $value;
	}

}
