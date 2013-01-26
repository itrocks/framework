<?php
namespace SAF\Framework;

class Acl_Link implements Contained
{

	//------------------------------------------------------------------------------------ $container
	/**
	 * @getter Aop::getObject
	 * @var Acl_Group
	 */
	public $container;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @getter Aop::getObject
	 * @var Acl_Group
	 */
	public $content;

	//------------------------------------------------------------------------------------- $priority
	/**
	 * @var integer
	 */
	public $priority;

	//--------------------------------------------------------------------------------------- dispose
	public function dispose()
	{
		$this->container->remove($this);
	}

	//------------------------------------------------------------------------------------- getParent
	/**
	 * Get parent object
	 *
	 * @return Acl_Group
	 */
	public function getParent()
	{
		return $this->container;
	}

	//------------------------------------------------------------------------------------- setParent
	/**
	 * Set parents object
	 *
	 * @param $object Acl_Group
	 * @return Acl_Link
	 */
	public function setParent($object)
	{
		$this->container = $object;
		return $this;
	}

}
