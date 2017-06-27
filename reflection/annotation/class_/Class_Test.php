<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Tests\Test;

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
