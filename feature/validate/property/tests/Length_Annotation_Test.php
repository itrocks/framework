<?php
namespace ITRocks\Framework\Feature\Validate\Property\Tests;

use ITRocks\Framework\Feature\Validate\Property\Length;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Test;

/**
 * Class Length_Annotation_Tests
 */
class Length_Annotation_Test extends Test
{

	//-------------------------------------------------------------------------------- $fail_property
	/** A test property that does not match #Length attribute */
	#[Length(5)]
	public string $fail_property = '1';

	//---------------------------------------------------------------------------- $reflection_object
	private ?Reflection_Class $reflection_object;

	//----------------------------------------------------------------------------- $success_property
	/** A test property that matches @length annotation */
	#[Length(10)]
	public string $success_property = '1234567890';

	//----------------------------------------------------------------------------------------- setUp
	/** Before each test */
	public function setUp() : void
	{
		$this->reflection_object = new Reflection_Class(__CLASS__);
	}

	//-------------------------------------------------------------------------------------- tearDown
	/** After each test */
	public function tearDown() : void
	{
		$this->reflection_object = null;
	}

	//------------------------------------------------------------------------------ testValidateFail
	/** Tests Length::validate() in a fail test case */
	public function testValidateFail() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$annotation = Length::of($this->reflection_object->getProperty('fail_property'));
		$actual     = $annotation->validate($this);
		static::assertFalse($actual);
	}

	//--------------------------------------------------------------------------- testValidateSuccess
	/** Tests Length::validate() in a success test case */
	public function testValidateSuccess() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$annotation = Length::of($this->reflection_object->getProperty('success_property'));
		$actual     = $annotation->validate($this);
		static::assertTrue($actual);
	}

}
