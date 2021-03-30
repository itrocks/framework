<?php
namespace ITRocks\Framework\Feature\Validate\Property\Tests;

use ITRocks\Framework\Feature\Validate\Property\Phone_Annotation;
use ITRocks\Framework\Phone\Phone_Format;
use ITRocks\Framework\Phone\Phone_Number_Exception;
use ITRocks\Framework\PHP\Reflection_Property;
use ITRocks\Framework\Tests\Test;
use stdClass;

class Phone_Annotation_Test extends Test
{

	//-------------------------------------------------------------------- messagePhoneNumberProvider
	/**
	 * @return array[]
	 */
	public function messagePhoneNumberProvider() : array
	{
		return [
			['061203', 'This phone number is not correct', 6],
			['zgre', 'This is not a number', 1],
			['062235', 'Number is too short', 2],
			['06223556291', 'Number is too long', 4]
		];
	}

	//------------------------------------------------------------------------------ testErrorMessage
	/**
	 * @dataProvider messagePhoneNumberProvider
	 * @param $phone_number  string
	 * @param $error_message string
	 * @param $error_code    integer
	 */
	public function testErrorMessage(string $phone_number, string $error_message, int $error_code)
	{
		$property_mock     = $this->createMock(Reflection_Property::class);
		$phone_format_mock = $this->createMock(Phone_Format::class);

		$phone_format_mock->expects($this->any())
			->method('isValid')
			->will($this->throwException(new Phone_Number_Exception($error_code, $error_message)));

		$property_mock->expects($this->once())->method('getName')->willReturn('foo_number');

		$phone_annotation = new Phone_Annotation(true ,$property_mock);
		$phone_annotation->phone_format = $phone_format_mock;

		$class             = new stdClass();
		$class->foo_number = $phone_number;

		$phone_annotation->validate($class);

		$this->assertEquals($error_message, $phone_annotation->reportMessage());
	}

	//----------------------------------------------------------------------- testValidatePhoneNumber
	/**
	 * @dataProvider validatePhoneNumberProvider
	 * @param $phone_number string
	 * @param $country_code ?string
	 * @param $is_valid     boolean
	 * @param $expected     boolean
	 */
	public function testValidatePhoneNumber(
		string $phone_number, ?string $country_code, bool $is_valid, bool $expected
	) {
		$property_mock     = $this->createMock(Reflection_Property::class);
		$phone_format_mock = $this->createMock(Phone_Format::class);

		$phone_format_mock->expects($this->once())->method('getCountryCode')->willReturn($country_code);
		$phone_format_mock->expects($this->once())->method('isValid')->willReturn($is_valid);
		$property_mock->expects($this->once())->method('getName')->willReturn('foo_number');

		$phone_annotation = new Phone_Annotation(true ,$property_mock);
		$phone_annotation->phone_format = $phone_format_mock;

		$class = new stdClass();
		$class->foo_number = $phone_number;

		$this->assertEquals($expected, $phone_annotation->validate($class));
	}

	//------------------------------------------------------------------- validatePhoneNumberProvider
	/**
	 * @return array[]
	 */
	public function validatePhoneNumberProvider() : array
	{
		return [
			['0622355629', '', true, true],
			['0622355629', '33', true, true],
			['061203', '', false, false],
			['zgre', '', false, false],
			['062235', '', false, false],
			['06223556291', '', false, false]
		];
	}

}
