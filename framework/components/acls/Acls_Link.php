<?php
namespace SAF\Framework;

class Acls_Link implements Component
{

	//------------------------------------------------------------------------------------ $container
	/**
	 * @getter Aop::getObject
	 * @var Acls_Group
	 */
	public $container;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @getter Aop::getObject
	 * @var Acls_Group
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
	 * @return Acls_Group
	 */
	public function getParent()
	{
		return $this->container;
	}

	//------------------------------------------------------------------------------------- setParent
	/**
	 * Set parents object
	 *
	 * @param $object Acls_Group
	 * @return Acls_Link
	 */
	public function setParent($object)
	{
		$this->container = $object;
		return $this;
	}

}
