<?php
namespace SAF\Framework;

class Acls_Link
{
	use Component;

	//------------------------------------------------------------------------------------ $container
	/**
	 * @composite
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

}
