<?php
namespace ITRocks\Framework\Feature\Validate\Property\Tests;

use ITRocks\Framework\Feature\Validate\Property\Max_Length_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Test;

/**
 * Validate widget max length annotation test
 */
class Max_Length_Annotation_Test extends Test
{

	//-------------------------------------------------------------------------------- $fail_property
	/**
	 * @max_length 3
	 * @var string
	 */
	public string $fail_property = 'abcdefg';

	//---------------------------------------------------------------------------- $reflection_object
	/**
	 * @var ?Reflection_Class
	 */
	private ?Reflection_Class $reflection_object;

	//----------------------------------------------------------------------------- $success_property
	/**
	 * @max_length 5
	 * @var string
	 */
	public string $success_property = 'abc';

	//----------------------------------------------------------------------------------------- setUp
	/**
	 * Before each test.
	 */
	public function setUp() : void
	{
		$this->reflection_object = new Reflection_Class(__CLASS__);
	}

	//-------------------------------------------------------------------------------------- tearDown
	/**
	 * After each test.
	 */
	public function tearDown() : void
	{
		$this->reflection_object = null;
	}

	//------------------------------------------------------------------------- testGetAnnotationName
	/**
	 * Tests method Max_Length_Annotation::getAnnotationName().
	 */
	public function testGetAnnotationName() : void
	{
		/** @var Reflection_Property $property_mock */
		$property_mock = $this->getMockBuilder(Reflection_Property::class)
			->disableOriginalConstructor()
			->getMock();
		$annotation = new Max_Length_Annotation('foo', $property_mock);

		$actual   = $annotation->getAnnotationName();
		$expected = 'max length';

		static::assertEquals($expected, $actual);
	}

	//------------------------------------------------------------------------------ testValidateFail
	/**
	 * Tests Max_Length_Annotation::validate() in success case.
	 */
	public function testValidateFail() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$annotation = Max_Length_Annotation::of($this->reflection_object->getProperty('fail_property'));
		$actual     = $annotation->validate($this);

		static::assertFalse($actual);
	}

	//--------------------------------------------------------------------------- testValidateSuccess
	/**
	 * Tests Max_Length_Annotation::validate() in success case.
	 */
	public function testValidateSuccess() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$annotation = Max_Length_Annotation::of(
			$this->reflection_object->getProperty('success_property')
		);
		$actual = $annotation->validate($this);

		static::assertTrue($actual);
	}

}
