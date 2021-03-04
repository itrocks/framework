<?php
namespace ITRocks\Framework\Feature\Validate\Property\Tests;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql\Link;
use ITRocks\Framework\Feature\Validate\Property\Unique_Annotation;
use ITRocks\Framework\PHP\Reflection_Property;
use ITRocks\Framework\Tests\Test;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

class Unique_Annotation_Test extends Test
{

	//------------------------------------------------------------------------------------- $dao_link
	/**
	 * @var Link|MockObject
	 */
	private $dao_link;

	//-------------------------------------------------------------------------------------- $old_dao
	/**
	 * @var Dao\Data_Link
	 */
	private $old_dao;

	//----------------------------------------------------------------------------------------- setUp
	public function setUp()
	{
		parent::setUp();
		$this->dao_link = $this->createMock(Link::class);
		$this->old_dao  = Dao::current();
		Dao::current($this->dao_link);
	}

	//-------------------------------------------------------------------------------------- tearDown
	public function tearDown()
	{
		parent::tearDown();
		Dao::current($this->old_dao);
		$this->dao_link = null;
	}

	//------------------------------------------------------------------------------ testErrorMessage
	public function testErrorMessage()
	{
		$property_mock = $this->createMock(Reflection_Property::class);

		$unique_annotation = new Unique_Annotation(false, $property_mock);

		$this->assertEquals('This value already exist', $unique_annotation->reportMessage());

	}

	//----------------------------------------------------------------- testValidateWithEmptyProperty
	public function testValidateWithEmptyProperty()
	{
		$this->dao_link->expects($this->never())->method('searchOne')->willReturn(null);

		$property_mock = $this->createMock(Reflection_Property::class);
		$property_mock->expects($this->once())->method('getName')->willReturn('name');

		$unique_annotation = new Unique_Annotation(true, $property_mock);

		$class = new stdClass();
		$class->name = null;
		$this->assertTrue($unique_annotation->validate($class));
	}

	//--------------------------------------------------------------- testValidateWithUnknownProperty
	public function testValidateWithUnknownProperty()
	{
		$this->dao_link->expects($this->never())->method('searchOne')->willReturn(null);

		$property_mock = $this->createMock(Reflection_Property::class);
		$property_mock->expects($this->once())->method('getName')->willReturn('name');

		$unique_annotation = new Unique_Annotation(true, $property_mock);

		$class = new stdClass();
		$class->foo = 'notname';

		$this->expectException('Exception');
		$this->expectExceptionMessage('The name property does not exist in stdClass object');
		$unique_annotation->validate($class);
	}

	//------------------------------------------------------------------------- testWithValidateFalse
	public function testWithValidateFalse()
	{
		$this->dao_link->expects($this->once())->method('searchOne')->willReturn(new stdClass());

		$property_mock = $this->createMock(Reflection_Property::class);
		$property_mock->expects($this->once())->method('getName')->willReturn('name');

		$unique_annotation = new Unique_Annotation(true, $property_mock);

		$class = new stdClass();
		$class->name = 'foo';
		$this->assertFalse($unique_annotation->validate($class));
	}

	//-------------------------------------------------------------------------- testWithValidateTrue
	public function testWithValidateTrue()
	{
		$property_mock = $this->createMock(Reflection_Property::class);
		$property_mock->expects($this->once())->method('getName')->willReturn('name');

		$unique_annotation = new Unique_Annotation(true, $property_mock);

		$class = new stdClass();
		$class->name = null;
		$this->assertTrue($unique_annotation->validate($class));
	}

}
