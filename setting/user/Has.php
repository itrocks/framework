<?php
namespace ITRocks\Framework\Setting\User;

use ITRocks\Framework;
use ITRocks\Framework\Reflection\Attribute\Class_\Extends_;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Setting\Has_Settings;

/**
 * For users that have settings
 *
 * @implements Has_Settings
 */
#[Extends_(Framework\User::class)]
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
