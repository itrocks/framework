<?php
namespace ITRocks\Framework\Feature\Validate\Property\Tests;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Mysql;
use ITRocks\Framework\Feature\Validate\Property\Unique;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tests\Test;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @todo testSimultaneousWriteOfDifferentValue
 * @todo testSimultaneousWriteOfSameValue
 * @todo testWriteMyself : when I write an existing value into a stored object which has the same id
 */
class Unique_Annotation_Test extends Test
{

	//------------------------------------------------------------------------------------- $dao_link
	private Mysql\Link|MockObject|null $dao_link;

	//-------------------------------------------------------------------------------------- $old_dao
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

	//----------------------------------------------------------------- testValidateWithEmptyProperty
	public function testValidateWithEmptyProperty() : void
	{
		$this->dao_link->expects($this->never())->method('searchOne');
		$object                  = new Test_Class();
		$object->unique_property = null;
		/** @noinspection PhpUnhandledExceptionInspection */
		$property = new Reflection_Property($object, 'unique_property');
		self::assertTrue(Unique::of($property)->validate($object));
	}

	//------------------------------------------------------------------------- testWithValidateFalse
	public function testWithValidateFalse() : void
	{
		$this->dao_link->expects($this->once())->method('searchOne')->willReturn(new Test_Class());
		$object                  = new Test_Class();
		$object->unique_property = 'value';
		/** @noinspection PhpUnhandledExceptionInspection */
		$property = new Reflection_Property($object, 'unique_property');
		self::assertFalse(Unique::of($property)->validate($object));
	}

	//-------------------------------------------------------------------------- testWithValidateTrue
	public function testWithValidateTrue() : void
	{
		$this->dao_link->expects($this->once())->method('searchOne')->willReturn(null);
		$object                  = new Test_Class();
		$object->unique_property = 'value';
		/** @noinspection PhpUnhandledExceptionInspection */
		$property = new Reflection_Property($object, 'unique_property');
		self::assertTrue(Unique::of($property)->validate($object));
	}

}
