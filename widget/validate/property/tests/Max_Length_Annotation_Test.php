<?php
namespace ITRocks\Framework\Widget\Validate\Property\Tests;

use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Widget\Validate\Property\Max_Length_Annotation;

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
	public $fail_property = 'abcdefg';

	//---------------------------------------------------------------------------- $reflection_object
	/**
	 * @var Reflection_Class
	 */
	private $reflection_object;

	//----------------------------------------------------------------------------- $success_property
	/**
	 * @max_length 5
	 * @var string
	 */
	public $success_property = 'abc';

	//----------------------------------------------------------------------------------------- setUp
	/**
	 * Before each test.
	 */
	public function setUp()
	{
		$this->reflection_object = new Reflection_Class(__CLASS__);
	}

	//-------------------------------------------------------------------------------------- tearDown
	/**
	 * After each test.
	 */
	public function tearDown()
	{
		$this->reflection_object = null;
	}

	//------------------------------------------------------------------------- testGetAnnotationName
	/**
	 * Tests method Max_Length_Annotation::getAnnotationName().
	 */
	public function testGetAnnotationName()
	{
		/** @var Reflection_Property $property_mock */
		$property_mock = $this->getMockBuilder(Reflection_Property::class)
			->disableOriginalConstructor()
			->getMock();
		$annotation = new Max_Length_Annotation('foo', $property_mock);

		$actual   = $annotation->getAnnotationName();
		$expected = 'max length';

		$this->assertEquals($expected, $actual);
	}

	//------------------------------------------------------------------------------ testValidateFail
	/**
	 * Tests Max_Length_Annotation::validate() in success case.
	 */
	public function testValidateFail()
	{
		$annotation = Max_Length_Annotation::of(
			$this->reflection_object->getProperty('fail_property')
		);
		$actual = $annotation->validate($this);
		$annotation->reportMessage();

		$this->assertFalse($actual);
	}

	//--------------------------------------------------------------------------- testValidateSuccess
	/**
	 * Tests Max_Length_Annotation::validate() in success case.
	 */
	public function testValidateSuccess()
	{
		$annotation = Max_Length_Annotation::of(
			$this->reflection_object->getProperty('success_property')
		);
		$actual = $annotation->validate($this);

		$this->assertTrue($actual);
	}

}
