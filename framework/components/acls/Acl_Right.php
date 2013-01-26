<?php
namespace SAF\Framework;

class Acl_Right implements Contained
{

	//---------------------------------------------------------------------------------------- $group
	/**
	 * @getter Aop::getObject
	 * @var Acl_Group
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
	 * @param $group Acl_Group
	 * @param $key   string
	 * @param $value string
	 */
	public function __construct(Acl_Group $group = null, $key = null, $value = null)
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
	 * @return Acl_Group
	 */
	public function getParent()
	{
		return $this->group;
	}

	//------------------------------------------------------------------------------------- setParent
	/**
	 * @param $object Acl_Group
	 * @return Acl_Right
	 */
	public function setParent($object)
	{
		$this->group = $object;
		return $this;
	}

}
