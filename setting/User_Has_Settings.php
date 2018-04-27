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
	 * @link Collection
	 * @override
	 * @user invisible
	 * @var User_Setting[]
	 */
	public $settings;

}
