<?php
namespace SAF\Framework;

class Acl_Group
{

	//-------------------------------------------------------------------------------------- $caption
	/**
	 * @var string
	 */
	public $caption;

	//--------------------------------------------------------------------------------------- $groups
	/**
	 * @getter Aop::getCollection
	 * @var multitype:Acl_Link
	 */
	public $contains;

	//--------------------------------------------------------------------------------------- $rights
	/**
	 * @getter Aop::getCollection
	 * @var multitype:Acl_Right
	 */
	public $rights;

}
