<?php
namespace SAF\Framework;

class Acls_Link
{
	use Component;

	//------------------------------------------------------------------------------------ $container
	/**
	 * @getter Aop::getObject
	 * @var Acls_Group
	 * @parent
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
