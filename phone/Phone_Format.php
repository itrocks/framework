<?php
namespace ITRocks\Framework\Phone;

use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Country;
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
	 * Country class name
	 *
	 * @var string
	 */
	public string $country_class = Country::class;

	//---------------------------------------------------------------------------- $phone_number_util
	/**
	 * @var PhoneNumberUtil
	 */
	private PhoneNumberUtil $phone_number_util;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration string[]
	 */
	public function __construct($configuration = [])
	{
		foreach ($configuration as $property_name => $value) {
			$this->$property_name = $value;
		}
		$this->phone_number_util = PhoneNumberUtil::getInstance();
	}

	//-------------------------------------------------------------------------------- getCountryCode
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @return string|null
	 */
	public function getCountryCode(object $object) : ?string
	{
		$country_code  = null;
		$country_class = $this->country_class;
		/** @noinspection PhpUnhandledExceptionInspection object */
		$reflection_class = new Reflection_Class($object);

		foreach ($reflection_class->getProperties() as $property) {
			/** @noinspection PhpUnhandledExceptionInspection getProperties */
			$property_value = $property->getValue($object);
			if (
				($property_value instanceof $country_class)
				&& isA($property_value, Has_Code::class)
			) {
				$country_code = $property_value->code;
			}
			elseif (is_object($property_value)) {
				$country_code = $this->getCountryCode($property_value);
			}
		}

		return $country_code;
	}

	//--------------------------------------------------------------------------------------- isValid
	/**
	 * Check if the phone number is valid with the country code
	 *
	 * @param $phone_number string
	 * @param $country_code string|null
	 * @return boolean
	 * @throws Phone_Number_Exception
	 */
	public function isValid(string $phone_number, ?string $country_code) : bool
	{
		// TODO We do not wait for a language, but for a country code, here
		$country_code = $country_code ?: Locale::get()->language;
		if ($country_code === 'en') {
			$country_code = 'gb';
		}

		try {
			$phone_number = $this->phone_number_util->parse($phone_number, strtoupper($country_code));
			return $this->phone_number_util->isValidNumber($phone_number);
		}
		catch (NumberParseException $exception) {
			throw new Phone_Number_Exception($exception->getErrorType(), $exception->getMessage());
		}
	}

}
