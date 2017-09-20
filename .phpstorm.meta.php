<?php
namespace PHPSTORM_META;

/**
 * To apply your changes to this file : simply close then re-launch PhpStorm
 */

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Null_Object;
use ITRocks\Framework\Mapper\Search_Object;

$STATIC_METHOD_TYPES = [
	Builder::create('') => [
		'' == '@'
	],
	// Dao::read will not work : it will check the first argument only
	// https://stackoverflow.com/questions/42104222/phpstorm-meta-file-syntax-for-static-methods-with-multiple-arguments
	/*
	Dao::read('') => [
		'' == '@'
	],
	*/
	Dao::searchOne('') => [
		'' == '@'
	],
	Dao::search('') => [
		'' == '@'
	],
	Null_Object::create('') => [
		'' == '@'
	],
	Search_Object::create('') => [
		'' == '@'
	],
	\PHPUnit_Framework_TestCase::createMock('') => [
		'' == '@|PHPUnit_Framework_MockObject_MockObject',
	],
	\PHPUnit_Framework_TestCase::getMock('') => [
		'' == '@|PHPUnit_Framework_MockObject_MockObject',
	],
];
