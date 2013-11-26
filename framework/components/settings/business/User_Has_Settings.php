<?php
namespace SAF\Framework;

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
	 * @link Collection
	 * @override
	 * @var User_Setting[]
	 */
	public $settings;

}
