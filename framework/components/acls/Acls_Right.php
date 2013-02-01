<?php
namespace SAF\Framework;

/**
 * @representative group, key
 */
class Acls_Right implements Contained
{

	//---------------------------------------------------------------------------------------- $group
	/**
	 * @getter Aop::getObject
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

	//--------------------------------------------------------------------------------------- dispose
	public function dispose()
	{
		$this->group->remove($this);
	}

	//------------------------------------------------------------------------------------- getParent
	/**
	 * @return Acls_Group
	 */
	public function getParent()
	{
		return $this->group;
	}

	//------------------------------------------------------------------------------------- setParent
	/**
	 * @param $object Acls_Group
	 * @return Acls_Right
	 */
	public function setParent($object)
	{
		$this->group = $object;
		return $this;
	}

}
