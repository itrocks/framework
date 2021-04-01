<?php
namespace ITRocks\Framework\Traits;

use Exception;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Phone\Phone_Format;
use ITRocks\Framework\Phone\Phone_Number_Exception;

/**
 * @validate validateNumber
 */
trait Has_Phone_Number
{

	//-------------------------------------------------------------------------------- validateNumber
	/**
	 * @param $object object
	 * @return string|bool
	 * @throws Exception
	 */
	public static function validateNumber(object $object): bool|string
	{
		try {
			if (!isA($object, Has_Number::class)) {
				throw new Exception(
					sprintf('This %s doesn\'t contains %s', get_class($object), Has_Number::class)
				);
			}
			$valid = Phone_Format::get()->isValid(
				$object->number,
				Phone_Format::get()->getCountryCode($object)
			);

			return $valid === false ? Loc::tr('This phone number is not correct') : true;
		}
		catch (Phone_Number_Exception $exception) {
			return $exception->getErrorType();
		}
	}

}
