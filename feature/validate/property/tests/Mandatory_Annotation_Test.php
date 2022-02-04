<?php
namespace ITRocks\Framework\Feature\Validate\Property\Tests;

use ITRocks\Framework\Reflection\Annotation\Property\Mandatory_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Test;
use stdClass;

/**
 * Validate widget mandatory annotation test
 */
class Mandatory_Annotation_Test extends Test
{

	//------------------------------------------------------------------------------- $empty_property
	/**
	 * @mandatory
	 */
	public mixed $empty_property;

	//------------------------------------------------------------------------------ $filled_property
	/**
	 * @mandatory true
	 */
	public string $filled_property = 'foo';

	//---------------------------------------------------------------------------- $reflection_object
	/**
	 * @var ?Reflection_Class
	 */
	private ?Reflection_Class $reflection_object;

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
	 * Tests Mandatory_Annotation::getAnnotationName().
	 */
	public function testGetAnnotationName()
	{
		/** @var $property_mock Reflection_Property */
		$property_mock = $this->getMockBuilder(Reflection_Property::class)
			->disableOriginalConstructor()
			->getMock();
		$annotation = new Mandatory_Annotation('foo', $property_mock);

		$actual   = $annotation->getAnnotationName();
		$expected = 'mandatory';

		static::assertEquals($expected, $actual);
	}

	//--------------------------------------------------------------------------------- testIsEmptyKo
	/**
	 * Tests Mandatory_Annotation::isEmpty() with a none empty property.
	 */
	public function testIsEmptyKo()
	{
		$annotation = Mandatory_Annotation::of(
			$this->reflection_object->getProperty('filled_property')
		);
		$actual = $annotation->isEmpty($this);

		static::assertFalse($actual);
	}

	//--------------------------------------------------------------------------------- testIsEmptyOk
	/**
	 * Tests Mandatory_Annotation::isEmpty() with an empty property.
	 */
	public function testIsEmptyOk()
	{
		$annotation = Mandatory_Annotation::of(
			$this->reflection_object->getProperty('empty_property')
		);
		$actual = $annotation->isEmpty($this);

		static::assertTrue($actual);
	}

	//------------------------------------------------------------------------------ testValidateNull
	/**
	 * Tests Mandatory_Annotation::validate() expecting null result.
	 */
	public function testValidateNull()
	{
		$annotation = Mandatory_Annotation::of($this->reflection_object->getProperty('empty_property'));
		$annotation->property = 'foo';
		$foo_param            = new stdClass();

		$actual = $annotation->validate($foo_param);
		static::assertNull($actual);
	}

}
