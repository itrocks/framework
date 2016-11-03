<?php
namespace ITRocks\Framework\Setting;

use /** @noinspection PhpUnusedAliasInspection @extends */ ITRocks\Framework\User;

/**
 * For users that have settings
 *
 * @business
 * @extends User
 * @implements Has_Settings
 */
trait User_Has_Settings
{

	//------------------------------------------------------------------------------------- $settings
	/**
	 * @user invisible
	 * @link Collection
	 * @override
	 * @var User_Setting[]
	 */
	public $settings;

}
