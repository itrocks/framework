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

use PHPUnit\Framework\TestCase;

function override($callable, $override) {
	return "override $callable $override";
}

$STATIC_METHOD_TYPES = [
	TestCase::createMock('') => [
		'' == '@',
	],
	TestCase::getMockBuilder('') => [
		'' == '@'
	]
];
