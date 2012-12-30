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
	public $value;

	//--------------------------------------------------------------------------------------- dispose
	public function dispose()
	{
		
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
	 * @param Acl_Group $object
	 * @return Acl_Right
	 */
	public function setParent($object)
	{
		$this->group = $object;
		return $this;
	}

}
