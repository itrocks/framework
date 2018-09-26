<?php
namespace ITRocks\Framework\Setting;

use ITRocks\Framework;
use ITRocks\Framework\Setting;

/**
 * For users that have settings
 *
 * @business
 * @extends Framework\User
 * @implements Has_Settings
 * @see Framework\User
 */
trait User_Has_Settings
{

	//------------------------------------------------------------------------------------- $settings
	/**
	 * @link Collection
	 * @override
	 * @user invisible
	 * @var Setting\User[]
	 */
	public $settings;

}
