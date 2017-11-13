<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Objects\Counter;
use ITRocks\Framework\Tests\Objects\Document;
use ITRocks\Framework\Tests\Objects\Order;
use ITRocks\Framework\Tests\Objects\Quote;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Traits\Has_Name;

/**
 * Class annotations unit tests
 */
class Class_Test extends Test
{

	//-------------------------------------------------------------------------------------- $subject
	/**
	 * @var Test_Object
	 */
	private $subject;

	//----------------------------------------------------------------------------------------- setUp
	/**
	 * Before each test.
	 */
	protected function setUp()
	{
		$this->subject = new Test_Object();
	}

	//----------------------------------------------------------------------- testExtendsDoNotInherit
	/**
	 * Test read of @extends (annotation that does not inherit)
	 */
	public function testExtendsDoNotInherit()
	{
		$extends = Extends_Annotation::allOf(new Reflection_Class(Order::class));
		$values  = [];
		foreach ($extends as $extend) {
			$values[] = $extend->values();
		}
		$this->assertEquals(print_r($values, true), print_r([[Document::class]], true), __METHOD__);
	}

	//------------------------------------------------------------------ testExtendsDoNotInheritAlone
	/**
	 * Test read of @extends (annotation that does not inherit)
	 */
	public function testExtendsDoNotInheritAlone()
	{
		$extends = Extends_Annotation::allOf(new Reflection_Class(Counter::class));
		$values  = [];
		foreach ($extends as $extend) {
			$values[] = $extend->values();
		}
		$this->assertEquals(print_r($values, true), print_r([[Has_Name::class]], true), __METHOD__);
	}

	//------------------------------------------------------------------ testExtendsDoNotInheritEmpty
	/**
	 * Test read of @extends (annotation that does not inherit)
	 */
	public function testExtendsDoNotInheritEmpty()
	{
		$extends = Extends_Annotation::allOf(new Reflection_Class(Quote::class));
		$values  = [];
		foreach ($extends as $extend) {
			$values[] = $extend->values();
		}
		$this->assertEquals(print_r($values, true), print_r([], true), __METHOD__);
	}

	//-------------------------------------------------------------------- testWriteAnnotationsCommit
	/**
	 * Tests Dao AOP in a transaction commit context.
	 */
	public function testWriteAnnotationsCommit()
	{
		$this->subject->data = 'test';
		Dao::begin();
		Dao::write($this->subject, Dao::only('data'));
		Dao::delete($this->subject);
		Dao::commit();
		$this->assertEquals(
			'test'
			. '+loc-before(data)+dis-before(data)'
			. '+loc-after(data)+dis-after(data)'
			. '+loc-after-commit1(data)+loc-after-commit2(data)',
			$this->subject->data,
			__METHOD__
		);
	}

	//------------------------------------------------------------------ testWriteAnnotationsRollBack
	/**
	 * Tests Dao AOP in a transaction rollback context.
	 */
	public function testWriteAnnotationsRollBack()
	{
		$this->subject->data = 'test';
		Dao::begin();
		Dao::write($this->subject, Dao::only('data'));
		Dao::delete($this->subject);
		Dao::rollback();
		$this->assertEquals(
			'test'
			. '+loc-before(data)+dis-before(data)'
			. '+loc-after(data)+dis-after(data)',
			$this->subject->data,
			__METHOD__
		);
	}

}
