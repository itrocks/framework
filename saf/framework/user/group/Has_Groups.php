<?php
namespace SAF\Framework\User\Group;

use SAF\Framework\User\Group;

/**
 * For business objects that need groups.
 *
 * Done for User, but can be used for other environments objects : eg organisations, etc.
 *
 * @business
 */
trait Has_Groups
{

	//--------------------------------------------------------------------------------------- $groups
	/**
	 * @link Map
	 * @var Group[]
	 */
	public $groups;

}
