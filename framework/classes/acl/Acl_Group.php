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
	 * @var multitype:Acl_Link
	 */
	public $contains;

	//--------------------------------------------------------------------------------------- $rights
	/**
	 * @var multitype:Acl_Right
	 */
	public $rights;

}
