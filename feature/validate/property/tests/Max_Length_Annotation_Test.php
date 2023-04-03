<?php
namespace ITRocks\Framework\Feature\Validate\Property\Tests;

use ITRocks\Framework\Feature\Validate\Property\Max_Length;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Test;

/**
 * Validate widget max length annotation test
 */
class Max_Length_Annotation_Test extends Test
{

	//-------------------------------------------------------------------------------- $fail_property
	#[Max_Length(3)]
	public string $fail_property = 'abcdefg';

	//---------------------------------------------------------------------------- $reflection_object
	private ?Reflection_Class $reflection_object;

	//----------------------------------------------------------------------------- $success_property
	#[Max_Length(5)]
	public string $success_property = 'abc';

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
	/** Tests Max_Length::validate() in success case */
	public function testValidateFail() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$annotation = Max_Length::of($this->reflection_object->getProperty('fail_property'));
		$actual     = $annotation->validate($this);

		static::assertFalse($actual);
	}

	//--------------------------------------------------------------------------- testValidateSuccess
	/** Tests Max_Length::validate() in success case */
	public function testValidateSuccess() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$annotation = Max_Length::of($this->reflection_object->getProperty('success_property'));
		$actual     = $annotation->validate($this);

		static::assertTrue($actual);
	}

}
