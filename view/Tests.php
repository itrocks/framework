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
class Tests extends Test
{

	//-------------------------------------------------------------------------------------- testLink
	public function testLink()
	{
		$user = new User();
		/** @noinspection PhpUndefinedFieldInspection simulated, for testing purpose */
		$user->id = 1;

		$this->method(__METHOD__);
		$this->assume(
			'output', View::link($user), '/ITRocks/Framework/User/1'
		);
		$this->assume(
			'explicit output', View::link($user, Feature::F_OUTPUT), '/ITRocks/Framework/User/1/output'
		);
		$this->assume(
			'edit', View::link($user, Feature::F_EDIT), '/ITRocks/Framework/User/1/edit'
		);
		$this->assume(
			'add', View::link(User::class), '/ITRocks/Framework/User'
		);
		$this->assume(
			'explicit add', View::link(User::class, Feature::F_ADD), '/ITRocks/Framework/User/add'
		);
		$this->assume(
			'list', View::link(Names::classToSet(User::class)), '/ITRocks/Framework/Users'
		);
		$this->assume(
			'explicit list',
			View::link(Names::classToSet(User::class), Feature::F_LIST),
			'/ITRocks/Framework/Users/dataList'
		);
	}

}
