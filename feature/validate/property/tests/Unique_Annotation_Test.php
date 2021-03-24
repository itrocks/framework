<?php
namespace ITRocks\Framework\Feature\Validate\Property\Tests;

use Exception;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Mysql;
use ITRocks\Framework\Feature\Validate\Property\Unique_Annotation;
use ITRocks\Framework\PHP\Reflection_Property;
use ITRocks\Framework\Tests\Test;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

class Unique_Annotation_Test extends Test
{

	//------------------------------------------------------------------------------------- $dao_link
	/**
	 * @var Mysql\Link|MockObject|null
	 */
	private Mysql\Link|MockObject|null $dao_link;

	//-------------------------------------------------------------------------------------- $old_dao
	/**
	 * @var Data_Link
	 */
	private Data_Link $old_dao;

	//----------------------------------------------------------------------------------------- setUp
	public function setUp() : void
	{
		parent::setUp();
		$this->dao_link = $this->createMock(Mysql\Link::class);
		$this->old_dao  = Dao::current();
		Dao::current($this->dao_link);
	}

	//-------------------------------------------------------------------------------------- tearDown
	public function tearDown() : void
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
	/**
	 * @throws Exception
	 */
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

	//------------------------------------------------------------------------- testWithValidateFalse
	/**
	 * @throws Exception
	 */
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
	/**
	 * @throws Exception
	 */
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
