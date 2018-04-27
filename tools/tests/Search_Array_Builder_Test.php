<?php
namespace ITRocks\Framework\Tools\Tests;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools;

/**
 * Search array builder test
 */
class Search_Array_Builder_Test extends Test
{

	//------------------------------------------------------------------------------------- testBuild
	public function testBuild()
	{
		$this->assertEquals(
			['property' => 'test'], (new Tools\Search_Array_Builder())->build('property', 'test'),
			__METHOD__ . '.simple'
		);
		$this->assertEquals(
			['property' => Func::andOp(['test', '%what'])],
			(new Tools\Search_Array_Builder())->build('property', 'test what'), __METHOD__ . '.and'
		);
		$this->assertEquals(
			['property' => ['test', 'what']],
			(new Tools\Search_Array_Builder())->build('property', 'test,what'), __METHOD__ . '.or'
		);
		$this->assertEquals(
			['property' => ['test', Func::andOp(['what', '%else'])]],
			(new Tools\Search_Array_Builder())->build('property', 'test,what else'), __METHOD__ . '.mix'
		);
	}

	//----------------------------------------------------------------------------- testBuildMultiple
	public function testBuildMultiple()
	{
		$this->assertEquals(
			Func::orOp(['pro1' => 'test', 'pro2' => 'test']),
			(new Tools\Search_Array_Builder())->buildMultiple(['pro1', 'pro2'], 'test'),
			__METHOD__ . '.simple'
		);
		$this->assertEquals(
			Func::andOp(
				[
					Func::orOp(['pro1' => 'test', 'pro2' => 'test']),
					Func::orOp(['pro1' => '%what', 'pro2' => '%what'])
				]
			), (new Tools\Search_Array_Builder())->buildMultiple(['pro1', 'pro2'], 'test what'),
			__METHOD__ . '.and'
		);
		$this->assertEquals(
			Func::orOp(
				[
					'pro1' => ['test', 'what'],
					'pro2' => ['test', 'what']
				]
			), (new Tools\Search_Array_Builder())->buildMultiple(['pro1', 'pro2'], 'test,what'),
			__METHOD__ . '.or'
		);
		$this->assertEquals(
			Func::orOp(
				[
					'pro1' => ['test', Func::andOp(['what', '%else'])],
					'pro2' => ['test', Func::andOp(['what', '%else'])]
				]
			), (new Tools\Search_Array_Builder())->buildMultiple(['pro1', 'pro2'], 'test,what else'),
			__METHOD__ . '.mix'
		);
	}

}
