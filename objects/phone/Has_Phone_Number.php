<?php
namespace ITRocks\Framework\Objects\Phone;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Phone\Phone_Format;
use ITRocks\Framework\Phone\Phone_Number_Exception;
use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Property\Alias;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Traits\Has_Number;
use ReflectionException;

/**
 * For classes that embed a phone number
 *
 * @override number @validate validateNumber
 */
#[Override('number', new Alias('phone'), new Mandatory(false))]
trait Has_Phone_Number
{
	use Has_Number;

	//-------------------------------------------------------------------------------- validateNumber
	/** @throws ReflectionException */
	public function validateNumber(Reflection_Property $property) : bool|string
	{
		$value = $property->getValue($this);
		if (!strlen($value)) {
			return true;
		}
		try {
			return Phone_Format::get()->isValid($value, Phone_Format::get()->getCountryCode($this))
				?: Loc::tr('This phone number is not correct');
		}
		catch (Phone_Number_Exception $exception) {
			return $exception->getErrorType();
		}
	}

}
