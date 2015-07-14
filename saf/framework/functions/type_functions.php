<?php

use SAF\Framework\Builder;

//-------------------------------------------------------------------------------------- class_tree
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

//--------------------------------------------------------------------------------------------- isA
/**
 * Returns true if an object / class /interface / trait is a class / interface / trait
 *
 * All parent classes, interfaces and traits are scanned recursively
 *
 * @param $object     string|object
 * @param $class_name string|object
 * @return boolean
 */
function isA($object, $class_name)
{
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
	$classes = class_parents($object) + class_uses($object);
	while ($classes) {
		$next_classes = [];
		foreach ($classes as $class) {
			if (is_a($class, $class_name, true)) return true;
			$next_classes += class_uses($class);
		}
		$classes = $next_classes;
	}
	return false;
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
