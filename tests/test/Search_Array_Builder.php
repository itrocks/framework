<?php
namespace SAF\Tests\Test;

use SAF\Framework\Search_Array_Builder;
use SAF\Framework\Unit_Tests\Unit_Test;

/**
 * Search array builder test
 */
class Search_Array_Builder extends Unit_Test
{

	//------------------------------------------------------------------------------------- testBuild
	public function testBuild()
	{
		$this->assume(
			__METHOD__ . '.simple',
			(new Search_Array_Builder())->build('property', 'test'),
			['property' => 'test']
		);
		$this->assume(
			__METHOD__ . '.and',
			(new Search_Array_Builder())->build('property', 'test what'),
			['property' => ['AND' => ['test', 'what']]]
		);
		$this->assume(
			__METHOD__ . '.or',
			(new Search_Array_Builder())->build('property', 'test,what'),
			['property' => ['test', 'what']]
		);
		$this->assume(
			__METHOD__ . '.mix',
			(new Search_Array_Builder())->build('property', 'test,what else'),
			['property' => ['test', 'AND' => ['what', 'else']]]
		);
	}

	//----------------------------------------------------------------------------- testBuildMultiple
	public function testBuildMultiple()
	{
		$this->assume(
			__METHOD__ . '.simple',
			(new Search_Array_Builder())->buildMultiple(['pro1', 'pro2'], 'test'),
			['OR' => ['pro1' => 'test', 'pro2' => 'test']]
		);
		$this->assume(
			__METHOD__ . '.and',
			(new Search_Array_Builder())->buildMultiple(['pro1', 'pro2'], 'test what'),
			[
				'AND' => [
					['OR' => ['pro1' => 'test', 'pro2' => 'test']],
					['OR' => ['pro1' => 'what', 'pro2' => 'what']]
				]
			]
		);
		$this->assume(
			__METHOD__ . '.or',
			(new Search_Array_Builder())->buildMultiple(['pro1', 'pro2'], 'test,what'),
			[
				'OR' => [
					'pro1' => ['test', 'what'],
					'pro2' => ['test', 'what']
				]
			]
		);
		$this->assume(
			__METHOD__ . '.mix',
			(new Search_Array_Builder())->buildMultiple(['pro1', 'pro2'], 'test,what else'),
			[
				'OR' => [
					'pro1' => ['test', 'AND' => ['what', 'else']],
					'pro2' => ['test', 'AND' => ['what', 'else']]
				]
			]
		);
	}

}
