<?php
namespace SAF\Framework;

/**
 * Link between two acls : one is the container and the other is the content
 */
class Acls_Link
{
	use Component;

	//------------------------------------------------------------------------------------ $container
	/**
	 * @composite
	 * @link Object
	 * @var Acls_Group
	 */
	public $container;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @link Object
	 * @var Acls_Group
	 */
	public $content;

	//------------------------------------------------------------------------------------- $priority
	/**
	 * @var integer
	 */
	public $priority;

}
