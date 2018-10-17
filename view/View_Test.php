<?php
namespace ITRocks\Framework\View;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\User;
use ITRocks\Framework\View;

/**
 * View tests class
 */
class View_Test extends Test
{

	//---------------------------------------------------------------------------------- providerLink
	/**
	 * @return array
	 * @see testLink
	 */
	public function providerLink()
	{
		$user = new User();
		/** @noinspection PhpUndefinedFieldInspection simulated, for testing purpose */
		$user->id = 1;
		return [
			'output'          => ['/ITRocks/Framework/User/1',      [$user]],
			'explicit output' => ['/ITRocks/Framework/User/1',      [$user, Feature::F_OUTPUT]],
			'edit'            => ['/ITRocks/Framework/User/1/edit', [$user, Feature::F_EDIT]],
			'add'             => ['/ITRocks/Framework/User',        [User::class]],
			'explicit add'    => ['/ITRocks/Framework/User/add',    [User::class, Feature::F_ADD]],
			'list'            => ['/ITRocks/Framework/Users',       [Names::classToSet(User::class)]],
			'explicit list'   => ['/ITRocks/Framework/Users',       [Names::classToSet(User::class), Feature::F_LIST]],
		];
	}

	//-------------------------------------------------------------------------------------- testLink
	/**
	 * @dataProvider providerLink
	 * @param $expect     string
	 * @param $parameters array
	 */
	public function testLink($expect, $parameters)
	{
		static::assertEquals($expect, View::link(...$parameters));
	}

}
