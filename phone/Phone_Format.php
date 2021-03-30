<?php
namespace ITRocks\Framework\Phone;

use ITRocks\Framework\Locale;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Traits\Has_Code;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class Phone_Format implements Configurable
{
	use Has_Get;

	//----------------------------------------------------------------------------------------- CONST
	public const COUNTRY_CLASS = 'country_class';

	//-------------------------------------------------------------------------------- $country_class
	/**
	 * @var string
	 */
	public $country_class;

	//---------------------------------------------------------------------------- $phone_number_util
	/**
	 * @var PhoneNumberUtil
	 */
	private $phone_number_util;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($configuration)
	{
		foreach ($configuration as $property_name => $value) {
			$this->{$property_name} = $value;
		}
		$this->phone_number_util = PhoneNumberUtil::getInstance();
	}

	//-------------------------------------------------------------------------------- getCountryCode
	/**
	 * @param $object object
	 * @return string|null
	 * @throws \ReflectionException
	 */
	public function getCountryCode($object) : ?string
	{
		$country          = null;
		$country_class    = $this->country_class;
		$reflection_class = new Reflection_Class($object);

		foreach ($reflection_class->getProperties() as $property) {
			$property_value = $property->getValue($object);
			if(
				$property_value instanceof $country_class
				&& isA($property_value, Has_Code::class)
			) {
				$country = $property_value->code;
			}
			else if(is_object($property_value)) {
				$country = $this->getCountryCode($property_value);
			}
		}

		return $country;
	}

	//--------------------------------------------------------------------------------------- isValid
	/**
	 * Check if the phone number is valid with the country code
	 *
	 * @param $phone_number string
	 * @param $country      string|null
	 * @return bool
	 * @throws Phone_Number_Exception
	 */
	public function isValid(string $phone_number, ?string $country) : bool
	{

		$country = $country ?? Locale::get()->language;

		try {
			$phone_number = $this->phone_number_util->parse($phone_number, strtoupper($country));
			return $this->phone_number_util->isValidNumber($phone_number);
		}
		catch (NumberParseException $exception) {
			throw new Phone_Number_Exception($exception->getErrorType(), $exception->getMessage());
		}
	}

}
