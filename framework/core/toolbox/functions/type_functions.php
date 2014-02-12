<?php

//-------------------------------------------------------------------------------- class_instanceof
/**
 * Returns true if an object / class (of one of its parents) uses (or is) a class
 *
 * All parent classes and interfaces are scanned recursively
 * This works if $class_name is an interface name or class name, but not if it is a trait name
 *
 * @param $object     object|string object or class name or interface name
 * @param $class_name object|string An object or object name or interface name
 * @return boolean
 */
function class_instanceof($object, $class_name)
{
	if (is_object($object))     $object     = get_class($object);
	if (is_object($class_name)) $class_name = get_class($class_name);
	return ($object === $class_name) || is_subclass_of($object, $class_name);
}

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
function class_tree($object, $classes = true, $traits = true, $interfaces = true, $self = true)
{
	$class_name = is_object($object) ? get_class($object) : $object;
	$tree = array();
	if ($classes) {
		$parent = get_parent_class($class_name);
		if (isset($parent)) {
			$tree = array_merge($tree, array($parent => $parent));
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
		$tree = array_merge($tree, class_tree($parent, $classes, $traits, $interfaces, false));
	}
	if ($self) {
		$tree[$class_name] = $class_name;
	}
	return $tree;
}

//-------------------------------------------------------------------------------- class_uses_trait
/**
 * Returns true if an object / class (or one of its parents) uses (or is) a trait
 *
 * All parent classes and traits are scanned recursively
 * This works if $trait_name is a class name too, but not if it is an interface name
 *
 * @param $object     object|string object or class name
 * @param $trait_name object|string a trait name
 * @return boolean
 */
function class_uses_trait($object, $trait_name)
{
	if (is_object($object))     $object     = get_class($object);
	if (is_object($trait_name)) $trait_name = get_class($trait_name);
	if ($object == $trait_name) {
		return true;
	}
	$traits = class_uses($object);
	if (in_array($trait_name, $traits)) {
		return true;
	}
	$parent_class = get_parent_class($object);
	if (!empty($parent_class) && class_uses_trait($parent_class, $trait_name)) {
		return true;
	}
	foreach (class_uses($object) as $trait) {
		if (class_uses_trait($trait, $trait_name)) {
			return true;
		}
	}
	return false;
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
	if (is_object($object))     $object     = get_class($object);
	if (is_object($class_name)) $class_name = get_class($class_name);
	if (is_a($object, $class_name, true)) return true;
	$classes = class_parents($object) + class_uses($object);
	while ($classes) {
		$next_classes = array();
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
	$classes = array($object) + $parents;
	$result = ($filter & _CLASS) ? $parents : array();
	do {
		$next_classes = array();
		foreach ($classes as $class) {
			if ($filter & _INTERFACE) $next_classes += class_implements($class);
			if ($filter & _TRAIT)     $next_classes += class_uses($class);
		}
		$classes = $next_classes;
		$result += $classes;
	} while($classes);
	return $result;
}
