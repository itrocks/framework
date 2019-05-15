<?php
namespace ITRocks\Framework\Address;

use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Traits\Has_Name;

/**
 * Person_Having_Name setDefaultName prepends civility
 */
class Person_Having_Name_Default_With_Civility implements Registerable
{

	//------------------------------------------------------------------------------- prependCivility
	/**
	 * @param $object Person_Having_Name|Has_Civility|Has_Name
	 */
	public static function prependCivility($object)
	{
		if ($object->civility && trim($object->first_name . SP . $object->last_name)) {
			$object->name = trim($object->civility->code . SP . $object->name);
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->afterMethod(
			[Person_Having_Name::class, 'setDefaultName'], [__CLASS__, 'prependCivility']
		);
	}

}
