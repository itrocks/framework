<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_\Tests;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Objects\A_Trait;
use ITRocks\Framework\Tests\Objects\Document;
use ITRocks\Framework\Tests\Objects\Has_Counter;
use ITRocks\Framework\Tests\Objects\Quote;
use ITRocks\Framework\Tests\Test;

/**
 * Class annotations unit tests
 *
 * @group functional
 */
class Class_Test extends Test
{

	//-------------------------------------------------------------------------------------- $subject
	/**
	 * @var Test_Object
	 */
	private Test_Object $subject;

	//----------------------------------------------------------------------------------------- setUp
	/**
	 * Before each test.
	 */
	protected function setUp() : void
	{
		$this->subject = new Test_Object();
	}

	//----------------------------------------------------------------- testExtendsEmptyParentExtends
	/**
	 * Test read of #Extends (attribute that does not inherit)
	 * class has no #Extends, parent has a #Extends : must result in empty
	 */
	public function testExtendsEmptyParentExtends() : void
	{
		$extends = Extend::of(new Reflection_Class(Quote::class));
		$values  = [];
		foreach ($extends as $extend) {
			$values[] = $extend->extends;
		}
		static::assertEquals(print_r([], true), print_r($values, true), __METHOD__);
	}

	//--------------------------------------------------------------- testExtendsExtendsParentExtends
	/**
	 * Test read of #Extends (attribute that does not inherit)
	 * class has #Extends, parent has an #Extends : must return the class #Extends alone
	 */
	public function testExtendsExtendsParentExtends() : void
	{
		$extends = Extend::of(new Reflection_Class(A_Trait::class));
		$values  = [];
		foreach ($extends as $extend) {
			$values[] = $extend->extends;
		}
		static::assertEquals(print_r([[Has_Counter::class]], true), print_r($values, true), __METHOD__);
	}

	//--------------------------------------------------------------- testExtendsExtendsWithoutParent
	/**
	 * Test read of #Extends (attribute that does not inherit)
	 * class has #Extends, no parent : must return the #Extends
	 */
	public function testExtendsExtendsWithoutParent() : void
	{
		$extends = Extend::of(new Reflection_Class(Has_Counter::class));
		$values  = [];
		foreach ($extends as $extend) {
			$values[] = $extend->extends;
		}
		static::assertEquals(print_r([[Document::class]], true), print_r($values, true), __METHOD__);
	}

	//--------------------------------------------------------------- testStoreNameWithParentAndTrait
	/**
	 * Gets the #Store_Name from a parent and a trait : the trait must override the parent
	 */
	public function testStoreNameWithParentAndTrait() : void
	{
		$store_name = Store::of(new Reflection_Class(Class_With_Trait_And_Parent::class))->value;
		static::assertEquals('test_trait_for_class_store_name', $store_name, __METHOD__);
	}

	//-------------------------------------------------------------------- testWriteAnnotationsCommit
	/**
	 * Tests Dao AOP in a transaction commit context.
	 */
	public function testWriteAnnotationsCommit() : void
	{
		$this->subject->data = 'test';
		Dao::begin();
		Dao::write($this->subject, Dao::only('data'));
		Dao::delete($this->subject);
		Dao::commit();
		static::assertEquals(
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
	public function testWriteAnnotationsRollBack() : void
	{
		$this->subject->data = 'test';
		Dao::begin();
		Dao::write($this->subject, Dao::only('data'));
		Dao::delete($this->subject);
		Dao::rollback();
		static::assertEquals(
			'test'
			. '+loc-before(data)+dis-before(data)'
			. '+loc-after(data)+dis-after(data)',
			$this->subject->data,
			__METHOD__
		);
	}

}
