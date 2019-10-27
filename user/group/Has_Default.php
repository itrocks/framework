<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\User\Group;

/**
 * @extends Group
 * @feature Select a default access group for registered users
 * @see Group
 */
trait Has_Default
{

	//-------------------------------------------------------------------------------------- $default
	/**
	 * @var boolean
	 */
	public $default = false;

}
