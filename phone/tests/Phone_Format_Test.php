<?php
namespace ITRocks\Framework\Phone\Tests;

use Iterator;
use ITRocks\Framework\Locale\Country;
use ITRocks\Framework\Phone\Phone_Format;
use ITRocks\Framework\Phone\Phone_Number_Exception;
use ITRocks\Framework\Tests\Test;

class Phone_Format_Test extends Test
{

	//--------------------------------------------------------------------------- phoneNumberProvider
	public function phoneNumberProvider() : Iterator
	{
		yield ['0622355629', 'FR', true];
		yield ['0622355629', '', true];
		yield ['06223556291', '', false];
		yield ['062235562', '', false];
	}

	//------------------------------------------------------------ testGetCountryCodeWithDefaultClass
	public function testGetCountryCodeWithDefaultClass()
	{
		$phone_format = new Phone_Format();

		$phone = new Dummy();
		$phone->country = new Country();
		$phone->country->code = 'FR';

		$this->assertEquals('FR', $phone_format->getCountryCode($phone));
	}

	//-------------------------------------------------------------- testGetCountryCodeWithReturnNull
	public function testGetCountryCodeWithReturnNull()
	{
		$phone_format = new Phone_Format();

		$phone = new Dummy();
		$phone->country = new Country();
		$phone->country->code = null;

		$this->assertEquals(null, $phone_format->getCountryCode($phone));
	}

	//----------------------------------------------------------------------------------- testIsValid
	/**
	 * @dataProvider phoneNumberProvider
	 * @param $phone_number string
	 * @param $country_code string
	 * @param $expected     boolean
	 * @throws Phone_Number_Exception
	 */
	public function testIsValid(string $phone_number, string $country_code, bool $expected)
	{
		$phone_format = new Phone_Format();

		$this->assertEquals($expected, $phone_format->isValid($phone_number, $country_code));
	}

}
