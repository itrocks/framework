<?php
namespace ITRocks\Framework\Widget\Confirm\Tests;

use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Widget\Confirm\Confirm_Controller;

/**
 * Class Confirm_Controller_Test.
 */
class Confirm_Controller_Test extends Test
{

	//--------------------------------------------------------------------------- testExtractPostData
	/**
	 * Test Confirm_Controller::extractPostData().
	 */
	public function testExtractPostData()
	{
		$post_data = [
			'foo' => [
				'bar' => [
					'foo' => [
						'bar' => 'value',
					],
				]
			],
			'bar' => 'value',
			'foobar' => [
				'foo' => 'bar',
			],
			'dummy' => ['a', 'set', 'of', 'values'],
		];

		$controller = new Confirm_Controller();
		$actual = $controller->extractPostData($post_data);
		$expected = [
			'foo[bar][foo][bar]' => 'value',
			'bar'                => 'value',
			'foobar[foo]'        => 'bar',
			'dummy[0]'           => 'a',
			'dummy[1]'           => 'set',
			'dummy[2]'           => 'of',
			'dummy[3]'           => 'values',
		];

		$this->assertEquals($expected, $actual);
	}

}
