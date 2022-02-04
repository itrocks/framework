<?php
namespace ITRocks\Framework\Feature\Validate\Property\Tests;

use ITRocks\Framework\Feature\Validate\Property\Length_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Test;
use ReflectionException;

/**
 * Class Length_Annotation_Tests
 */
class Length_Annotation_Test extends Test
{

	//-------------------------------------------------------------------------------- $fail_property
	/**
	 * A test property that does not match @length annotation.
	 *
	 * @length 5
	 * @var string
	 */
	public string $fail_property = '1';

	//---------------------------------------------------------------------------- $reflection_object
	/**
	 * @var ?Reflection_Class
	 */
	private ?Reflection_Class $reflection_object;

	//----------------------------------------------------------------------------- $success_property
	/**
	 * A test property that matches @length annotation.
	 *
	 * @length 10
	 * @var string
	 */
	public string $success_property = '1234567890';

	//----------------------------------------------------------------------------------------- setUp
	/**
	 * Before each test
	 */
	public function setUp() : void
	{
		$this->reflection_object = new Reflection_Class(__CLASS__);
	}

	//-------------------------------------------------------------------------------------- tearDown
	/**
	 * After each test
	 */
	public function tearDown() : void
	{
		$this->reflection_object = null;
	}

	//------------------------------------------------------------------------- testGetAnnotationName
	/**
	 * Tests method Length_Annotation::getAnnotationName()
	 */
	public function testGetAnnotationName()
	{
		/** @var Reflection_Property $property_mock */
		$property_mock = $this->getMockBuilder(Reflection_Property::class)
			->disableOriginalConstructor()
			->getMock();
		$annotation = new Length_Annotation('foo', $property_mock);

		$actual   = $annotation->getAnnotationName();
		$expected = 'length';

		static::assertEquals($expected, $actual);
	}

	//------------------------------------------------------------------------------ testValidateFail
	/**
	 * Tests Length_Annotation::validate() in a fail test case.
	 */
	public function testValidateFail()
	{
		$annotation = Length_Annotation::of($this->reflection_object->getProperty('fail_property'));
		$actual     = $annotation->validate($this);

		static::assertFalse($actual);
	}

	//--------------------------------------------------------------------------- testValidateSuccess
	/**
	 * Tests Length_Annotation::validate() in a success test case.
	 */
	public function testValidateSuccess()
	{
		$annotation = Length_Annotation::of(
			$this->reflection_object->getProperty('success_property')
		);
		$actual = $annotation->validate($this);

		static::assertTrue($actual);
	}

}
