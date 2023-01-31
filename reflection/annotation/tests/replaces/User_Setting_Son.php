<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Replaces;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Setting;
use ITRocks\Framework\User;

/**
 * A son for the class Dependency, with some replacements
 *
 * TODO A unit test to check that the getter is called once and both properties are always right
 */
#[Store('user_setting_sons')]
class User_Setting_Son extends Setting\User
{

	//------------------------------------------------------------------------------------------ $guy
	/**
	 * @composite
	 * @link Object
	 * @override
	 * @replaces user
	 * @var User
	 */
	public User $guy;

}
