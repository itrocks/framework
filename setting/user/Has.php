<?php
namespace ITRocks\Framework\Setting\User;

use ITRocks\Framework;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Setting\Has_Settings;

/**
 * For users that have settings
 *
 * @business
 * @extends Framework\User
 * @implements Has_Settings
 * @see Framework\User
 */
trait Has
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
