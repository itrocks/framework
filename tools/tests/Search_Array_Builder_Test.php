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
		$this->assume(
			__METHOD__ . '.simple',
			(new Tools\Search_Array_Builder())->build('property', 'test'),
			['property' => 'test']
		);
		$this->assume(
			__METHOD__ . '.and',
			(new Tools\Search_Array_Builder())->build('property', 'test what'),
			['property' => Func::andOp(['test', '%what'])]
		);
		$this->assume(
			__METHOD__ . '.or',
			(new Tools\Search_Array_Builder())->build('property', 'test,what'),
			['property' => ['test', 'what']]
		);
		$this->assume(
			__METHOD__ . '.mix',
			(new Tools\Search_Array_Builder())->build('property', 'test,what else'),
			['property' => ['test', Func::andOp(['what', '%else'])]]
		);
	}

	//----------------------------------------------------------------------------- testBuildMultiple
	public function testBuildMultiple()
	{
		$this->assume(
			__METHOD__ . '.simple',
			(new Tools\Search_Array_Builder())->buildMultiple(['pro1', 'pro2'], 'test'),
			Func::orOp(['pro1' => 'test', 'pro2' => 'test'])
		);
		$this->assume(
			__METHOD__ . '.and',
			(new Tools\Search_Array_Builder())->buildMultiple(['pro1', 'pro2'], 'test what'),
			Func::andOp([
				Func::orOp(['pro1' => 'test',  'pro2' => 'test']),
				Func::orOp(['pro1' => '%what', 'pro2' => '%what'])
			])
		);
		$this->assume(
			__METHOD__ . '.or',
			(new Tools\Search_Array_Builder())->buildMultiple(['pro1', 'pro2'], 'test,what'),
			Func::orOp([
				'pro1' => ['test', 'what'],
				'pro2' => ['test', 'what']
			])
		);
		$this->assume(
			__METHOD__ . '.mix',
			(new Tools\Search_Array_Builder())->buildMultiple(['pro1', 'pro2'], 'test,what else'),
			Func::orOp([
				'pro1' => ['test', Func::andOp(['what', '%else'])],
				'pro2' => ['test', Func::andOp(['what', '%else'])]
			])
		);
	}

}
