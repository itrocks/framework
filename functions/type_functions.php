<?php

use ITRocks\Framework\Builder;

//--------------------------------------------------------------------------------------- classTree
/**
 * Gets full class names tree, recursively
 *
 * @param $object     object|string object or class name or interface name or trait name
 * @param $classes    boolean get parent classes list
 * @param $traits     boolean get parent traits list
 * @param $interfaces boolean get parent interfaces list
 * @param $self       boolean get the object / class name itself
 * @return string[] keys and values are classes / traits / interfaces names
 */
function classTree($object, $classes = true, $traits = true, $interfaces = true, $self = true)
{
	$class_name = is_object($object) ? get_class($object) : $object;
	$tree = [];
	if ($classes) {
		$parent = get_parent_class($class_name);
		if (isset($parent)) {
			$tree = array_merge($tree, [$parent => $parent]);
		}
	}
	if ($traits) {
		$parents = class_uses($class_name);
		$tree = array_merge($tree, array_combine($parents, $parents));
	}
	if ($interfaces) {
		$parents = class_implements($class_name);
		$tree = array_merge($tree, array_combine($parents, $parents));
	}
	foreach ($tree as $parent) {
		$tree = array_merge($tree, classTree($parent, $classes, $traits, $interfaces, false));
	}
	if ($self) {
		$tree[$class_name] = $class_name;
	}
	return $tree;
}

//--------------------------------------------------------------------------------------------- cmp
/**
 * Returns 0 if $v1 === $v2, -1 if $v1 < $v2, 1 if $v1 > $v2 : use it for smaller uasort() callbacks
 *
 * @param $v1     mixed
 * @param $v2     mixed
 * @param $strict boolean true for strict comparison (type must be the same), else false
 * @return integer -1, 0 or 1
 */
function cmp($v1, $v2, $strict = true)
{
	if ($strict ? ($v1 === $v2) : ($v1 == $v2)) {
		return 0;
	}
	return ($v1 < $v2) ? -1 : 1;
}

//-------------------------------------------------------------------------------------- instanceIn
/**
 * Returns if there is an instance of class in the given array of objects
 *
 * @param $class_name_or_object string|object
 * @param $objects              object[]
 * @return object|null
 */
function instanceIn($class_name_or_object, array $objects)
{
	foreach ($objects as $object) {
		if ($object instanceof $class_name_or_object) {
			return $object;
		}
	}
	return null;
}

//--------------------------------------------------------------------------------------------- isA
/**
 * Returns true if an object / class /interface / trait is a class / interface / trait
 *
 * All parent classes, interfaces and traits are scanned recursively
 *
 * @param $object     string|object
 * @param $class_name string|object|string[]|object[] If multiple, result is true for any of them
 * @return boolean
 */
function isA($object, $class_name)
{
	if (is_array($class_name)) {
		foreach ($class_name as $a_class_name) {
			if (isA($object, $a_class_name)) {
				return true;
			}
		}
	}
	else {
		if (is_string($object)) {
			$object = Builder::className($object);
		}
		elseif (is_object($object)) {
			$object = get_class($object);
		}
		else {
			return false;
		}
		if (is_object($class_name)) {
			$class_name = get_class($class_name);
		}
		if (is_a($object, $class_name, true)) {
			return true;
		}
		if (
			   !class_exists($object)     && !interface_exists($object)     && !trait_exists($object)
			|| !class_exists($class_name) && !interface_exists($class_name) && !trait_exists($class_name)
		) {
			return false;
		}
		$classes = class_parents($object) + class_uses($object);
		while ($classes) {
			$next_classes = [];
			foreach ($classes as $class) {
				if (is_a($class, $class_name, true)) return true;
				$next_classes += class_uses($class);
			}
			$classes = $next_classes;
		}
	}
	return false;
}

//--------------------------------------------------------------------------------- isStrictInteger
/**
 * Returns true if $value is a strict integer.
 * Same as isStrictNumeric, but :
 * - must not have decimal char
 *
 * @param $value string
 * @return boolean
 */
function isStrictInteger($value)
{
	return isStrictNumeric($value, false);
}

//--------------------------------------------------------------------------------- isStrictNumeric
/**
 * Returns true if $value is a strict numeric.
 * Same as php's is_numeric, but :
 * - must not start with '+', '0' ('0' alone and '0.xxx' are allowed)
 * - exponential part is not allowed, thus 123.45e6 and 123E6 are not a valid numeric value
 * - if decimal not allowed, must not have '.' char
 * - if signed not allowed, must not start with '-' char
 *
 * @param $value           mixed
 * @param $decimal_allowed boolean
 * @param $signed_allowed  boolean
 * @return boolean
 */
function isStrictNumeric(mixed $value, bool $decimal_allowed = true, bool $signed_allowed = true)
	: bool
{
	if (is_integer($value) || ($decimal_allowed && is_float($value))) {
		return $signed_allowed || ($value >= 0);
	}
	if (
		(is_float($value) && !$decimal_allowed)
		|| !is_numeric($value)
	) {
		return false;
	}
	$has_decimal = str_contains($value, '.');
	if ($has_decimal) {
		if (str_starts_with($value, '.')) {
			$value = '0' . $value;
		}
		elseif (str_starts_with($value, '-.')) {
			$value = '-0' . substr($value, 1);
		}
		$value = rtrim(rtrim($value, '0'), DOT);
	}
	$numeric = $decimal_allowed ? floatval($value) : intval($value);

	return !strcmp($value, $numeric)
		&& ($decimal_allowed || !$has_decimal)
		&& ($signed_allowed || !str_starts_with($value, '-'));
}

//------------------------------------------------------------------------- isStrictUnsignedInteger
/**
 * Returns true iv $value is a strict integer.
 * Same as isStrictNumeric, but :
 * - must not have decimal char
 *
 * @param $value string
 * @return boolean
 */
function isStrictUnsignedInteger($value)
{
	return isStrictNumeric($value, false, false);
}

//------------------------------------------------------------------------------------------ maxSet
/**
 * Returns the maximal value of $arguments
 *
 * @param $arguments float|float[]|integer|integer[]
 * @return integer|null null if there is not any real value into arguments
 */
function maxSet($arguments)
{
	$maximum = null;
	foreach (func_get_args() as $argument) {
		if (is_array($argument)) {
			$argument = call_user_func_array(__FUNCTION__, $argument);
		}
		if (($argument !== false) && !is_null($argument)) {
			$maximum = isset($maximum) ? max($argument, $maximum) : $argument;
		}
	}
	return $maximum;
}

//------------------------------------------------------------------------------------------ minSet
/**
 * Returns the minimal value of $arguments
 *
 * @param $arguments float|float[]|integer|integer[]
 * @return integer|null null if there is not any real value into arguments
 */
function minSet($arguments)
{
	$minimum = null;
	foreach (func_get_args() as $argument) {
		if (is_array($argument)) {
			$argument = call_user_func_array(__FUNCTION__, $argument);
		}
		if (($argument !== false) && !is_null($argument)) {
			$minimum = isset($minimum) ? min($argument, $minimum) : $argument;
		}
	}
	return $minimum;
}

define('_ALL',       65535);
define('_CLASS',     1);
define('_INTERFACE', 2);
define('_TRAIT',     4);

//----------------------------------------------------------------------------------------- parents
/**
 * Returns all parents (classes, interfaces and traits) of the class or object
 *
 * Result order is : classes first, then interfaces and traits from the child to the parent
 *
 * @param $object string|object
 * @param $filter integer _ALL, _CLASS | _INTERFACE | _TRAIT
 * @return string[]
 */
function parents($object, $filter = _ALL)
{
	if (is_object($object)) $object = get_class($object);
	$parents = class_parents($object);
	$classes = [$object] + $parents;
	$result = ($filter & _CLASS) ? $parents : [];
	do {
		$next_classes = [];
		foreach ($classes as $class) {
			if ($filter & _INTERFACE) $next_classes += class_implements($class);
			if ($filter & _TRAIT)     $next_classes += class_uses($class);
		}
		$classes = $next_classes;
		$result += $classes;
	} while($classes);
	return $result;
}
