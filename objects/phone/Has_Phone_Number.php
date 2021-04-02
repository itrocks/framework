<?php
namespace ITRocks\Framework\Objects\Phone;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Phone\Phone_Format;
use ITRocks\Framework\Phone\Phone_Number_Exception;
use ITRocks\Framework\Reflection\Reflection_Property;

trait Has_Phone_Number
{

	//-------------------------------------------------------------------------------- validateNumber
	/**
	 * @param $property Reflection_Property
	 * @return string|bool
	 * @throws \Exception
	 */
	public function validateNumber(Reflection_Property $property) : bool|string
	{
		try {
			$valid = Phone_Format::get()->isValid(
				$property->getValue($this),
				Phone_Format::get()->getCountryCode($this)
			);

			return $valid === false ? Loc::tr('This phone number is not correct') : true;
		}
		catch (Phone_Number_Exception $exception) {
			return $exception->getErrorType();
		}
	}

}
