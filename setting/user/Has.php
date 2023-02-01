<?php
namespace ITRocks\Framework\Setting\User;

use ITRocks\Framework;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Setting\Has_Settings;

/**
 * For users that have settings
 *
 * @extends Framework\User
 * @implements Has_Settings
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
	public array $settings;

}
