<?php
namespace SAF\Framework\Setting;

/**
 * For users that have settings
 *
 * @business
 * @extends SAF\Framework\User
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
