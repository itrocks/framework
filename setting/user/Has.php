<?php
namespace ITRocks\Framework\Setting\User;

use ITRocks\Framework;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Reflection\Attribute\Class_\Implement;
use ITRocks\Framework\Reflection\Attribute\Property\Component;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Setting\Has_Settings;

/**
 * For users that have settings
 */
#[Extend(Framework\User::class), Implement(Has_Settings::class)]
trait Has
{

	//------------------------------------------------------------------------------------- $settings
	/**
	 * @override
	 * @var Setting\User[]
	 */
	#[Component, User(User::INVISIBLE)]
	public array $settings;

}
