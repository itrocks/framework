<?php
namespace SAF\Framework\Setting\Module;

/**
 * For users that have settings
 *
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
