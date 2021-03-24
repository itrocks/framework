<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_\Tests;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Annotation\Class_\Extends_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Objects\A_Trait;
use ITRocks\Framework\Tests\Objects\Document;
use ITRocks\Framework\Tests\Objects\Has_Counter;
use ITRocks\Framework\Tests\Objects\Quote;
use ITRocks\Framework\Tests\Test;
use ReflectionException;

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
	private $subject;

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
	 * Test read of @extends (annotation that does not inherit)
	 * class has no @extends, parent has a @extends : must result in empty
	 *
	 * @throws ReflectionException
	 */
	public function testExtendsEmptyParentExtends()
	{
		$extends = Extends_Annotation::allOf(new Reflection_Class(Quote::class));
		$values  = [];
		foreach ($extends as $extend) {
			$values[] = $extend->values();
		}
		static::assertEquals(print_r($values, true), print_r([], true), __METHOD__);
	}

	//--------------------------------------------------------------- testExtendsExtendsParentExtends
	/**
	 * Test read of @extends (annotation that does not inherit)
	 * class has @extends, parent has an @extends : must return the class @extends alone
	 *
	 * @throws ReflectionException
	 */
	public function testExtendsExtendsParentExtends()
	{
		$extends = Extends_Annotation::allOf(new Reflection_Class(A_Trait::class));
		$values  = [];
		foreach ($extends as $extend) {
			$values[] = $extend->values();
		}
		static::assertEquals(print_r($values, true), print_r([[Has_Counter::class]], true), __METHOD__);
	}

	//--------------------------------------------------------------- testExtendsExtendsWithoutParent
	/**
	 * Test read of @extends (annotation that does not inherit)
	 * class has @extends, no parent : must return the @extends
	 *
	 * @throws ReflectionException
	 */
	public function testExtendsExtendsWithoutParent()
	{
		$extends = Extends_Annotation::allOf(new Reflection_Class(Has_Counter::class));
		$values  = [];
		foreach ($extends as $extend) {
			$values[] = $extend->values();
		}
		static::assertEquals(print_r($values, true), print_r([[Document::class]], true), __METHOD__);
	}

	//--------------------------------------------------------------- testStoreNameWithParentAndTrait
	/**
	 * Gets the @store_name from a parent and a trait : the trait must override the parent
	 *
	 * @throws ReflectionException
	 */
	public function testStoreNameWithParentAndTrait()
	{
		$store_name = Store_Name_Annotation::of(new Reflection_Class(
			Class_With_Trait_And_Parent::class
		));
		static::assertEquals($store_name->value, 'test_trait_for_class_store_name', __METHOD__);
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
	public function testWriteAnnotationsRollBack()
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
