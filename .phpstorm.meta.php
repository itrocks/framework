<?php
namespace PHPSTORM_META {

	use ITRocks\Framework\Builder;
	use ITRocks\Framework\Dao;

	$STATIC_METHOD_TYPES = [
		Builder::create('') => [
			'' == '@'
		],
		Dao::searchOne('') => [
			'' == '@'
		],
		Dao::search('') => [
			'' == '@'
		],
		\PHPUnit_Framework_TestCase::createMock('') => [
			'' == '@|PHPUnit_Framework_MockObject_MockObject',
		],
		\PHPUnit_Framework_TestCase::getMock('') => [
			'' == '@|PHPUnit_Framework_MockObject_MockObject',
		],
	];

}
