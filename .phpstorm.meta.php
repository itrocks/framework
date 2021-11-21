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

use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use mysqli_result;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql;
use ITRocks\Framework\Dao\Option\Sort;
use ITRocks\Framework\Mapper\Null_Object;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Plugin;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Call_Stack;
use ITRocks\Framework\Tools\Current;
use ITRocks\Framework\View\Html\Template;
use PHPUnit\Framework\TestCase;

function override($callable, $override) {
	return "override $callable $override";
}

$STATIC_METHOD_TYPES = [
	mysqli_result::fetch_object('') => [
		'' == '@'
	],
	TestCase::createMock('') => [
		'' == '@',
	],
	TestCase::getMockBuilder('') => [
		'' == '@'
	]
];
