<?php
namespace PHPSTORM_META;

/**
 * This allows to dynamically set the class of the returned value for these methods, keeping the
 * one (class name or class of the object) used as the first non-array argument passed to the method
 *
 * Install the OXID Plugin into PHP Storm to enabled this feature
 *
 * To apply your changes to this file : simply close then re-launch PhpStorm
 */

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Null_Object;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\View\Html\Template;

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
	Dao::write('') => [
		'' == '@'
	],
	Null_Object::create('') => [
		'' == '@'
	],
	Parameters::getObject('') => [
		'' == '@'
	],
	Search_Object::create('') => [
		'' == '@'
	],
	Template::getParentObject('') => [
		'' == '@'
	],
	\PHPUnit_Framework_TestCase::createMock('') => [
		'' == '@|PHPUnit_Framework_MockObject_MockObject',
	],
	\PHPUnit_Framework_TestCase::getMock('') => [
		'' == '@|PHPUnit_Framework_MockObject_MockObject',
	],
];
