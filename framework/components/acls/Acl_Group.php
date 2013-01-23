<?php
namespace SAF\Framework;

class Acl_Group
{

	//-------------------------------------------------------------------------------------- $caption
	/**
	 * @var string
	 */
	public $caption;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var string
	 * @values user,
	 */
	public $type;

	//--------------------------------------------------------------------------------------- $groups
	/**
	 * @getter Aop::getCollection
	 * @var multitype:Acl_Link
	 */
	public $content;

	//--------------------------------------------------------------------------------------- $rights
	/**
	 * @getter Aop::getCollection
	 * @var multitype:Acl_Right
	 */
	public $rights;

}
