<?php
namespace ITRocks\Framework\Objects\Phone;

use ITRocks\Framework\Objects\Phone\Tests\Phone_Dummy;
use ITRocks\Framework\Phone\Phone_Format;
use ITRocks\Framework\Plugin;
use ITRocks\Framework\Property\Reflection_Property;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tests\Test;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class Phone_Number_Test extends Test
{

	//--------------------------------------------------------------------------------- $phone_format
	private Phone_Format|MockObject|null $phone_format;

	//----------------------------------------------------------------------------- $phone_format_old
	private Phone_Format|Plugin|null $phone_format_old;

	//-------------------------------------------------------------------------- $reflection_property
	private MockObject|Reflection_Property $reflection_property;

	//----------------------------------------------------------------------------------------- setUp
	public function setUp() : void
	{
		parent::setUp();
		$this->phone_format_old = Phone_Format::get();
		$this->phone_format = $this->createMock(Phone_Format::class);
		$this->reflection_property = $this->createMock(Reflection_Property::class);
		Session::current()->plugins->set($this->phone_format, Phone_Format::class);
	}

	//-------------------------------------------------------------------------------------- tearDown
	public function tearDown() : void
	{
		$this->phone_format = null;
		Session::current()->plugins->set($this->phone_format_old, Phone_Format::class);
		parent::tearDown();
	}

	//--------------------------------------------------------------------- testPhoneNumberIsNotValid
	/**
	 * @throws ReflectionException
	 */
	public function testPhoneNumberIsNotValid()
	{
		$phone_number = '';
		$phone = new Phone_Dummy($phone_number);

		$this->reflection_property->expects($this->once())
			->method('getValue')
			->willReturn($phone_number);

		$this->assertEquals(
			'This phone number is not correct',
			$phone->validateNumber($this->reflection_property)
		);
	}

	//-------------------------------------------------------------------- testWithPhoneNumberIsValid
	/**
	 * @throws ReflectionException
	 */
	public function testWithPhoneNumberIsValid()
	{
		$phone_number = '0622355629';
		$phone = new Phone_Dummy($phone_number);

		$this->phone_format->expects($this->any())
			->method('isValid')
			->willReturn(true);

		$this->reflection_property->expects($this->once())
			->method('getValue')
			->willReturn($phone_number);

		$this->assertTrue($phone->validateNumber($this->reflection_property));
	}

}
