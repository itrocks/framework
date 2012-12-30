<?php
namespace SAF\Framework;

class Acl_Link
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

}
