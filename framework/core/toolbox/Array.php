<?php

use SAF\Framework\Object_Builder;

//------------------------------------------------------------------------------ arrayDiffRecursive
function arrayDiffRecursive($array1, $array2, $show_type = false)
{
	$diff = array();
	foreach ($array1 as $key => $value) {
		if (!array_key_exists($key, $array2)) {
			$diff[$key] = $value;
		}
		elseif (is_array($value)) {
			if (!is_array($array2[$key])) {
				$diff[$key] = $value;
			}
			else {
				$sub_diff = arrayDiffRecursive($value, $array2[$key]);
				if ($sub_diff !== false) {
					$diff[$key] = $sub_diff;
				}
			}
		}
		elseif (is_array($array2[$key])) {
			$diff[$key] = $value;
		}
		elseif ($array2[$key] !== $value) {
			$diff[$key] = "$value";
			if ($show_type && (gettype($value) !== gettype($array2[$key]))) {
				$diff[$key] .= "(" . gettype($value) . ")";
			}
		}
	}
	return $diff ? $diff : false;
}

//------------------------------------------------------------------------ function arrayFormRevert
/**
 * Reverts an array comming from a dynmamic form result
 *
 * @example
 * Source array is array($field_name => array($n => $value))
 * Destination array is array($n => array($field_name => $value))
 * other example that works
 * Source array is array($field_name => array($sub_field_name => array($n => $value))
 * Destination array is array($n => array($field_name => array($sub_field_name => $value))
 * @param array $array
 * return array
 */
function arrayFormRevert($array)
{
	if (is_array($array)) {
		$result = array ();
		foreach ($array as $field_name => $sub_array) {
			if (is_array($sub_array)) {
				foreach ($sub_array as $n => $value) {
					if (!is_array($value)) {
						$result[$n][$field_name] = $value;
					} else {
						foreach ($value as $n2 => $value2) {
							$result[$n2][$field_name][$n] = $value2;
						}
					}
				}
			}
		}
		return $result;
	} else {
		return $array;
	}
}

//----------------------------------------------------------------------------- arrayMergeRecursive
/**
 * Merges two arrays, with recursion
 *
 * Elements of $array1 and $array2 with same index (even if numeric) : $array2 element replaces $array1 element.
 * If $array2 element is an array : merge $array1 and $array2 array element, recursively.
 *
 * @param array $array1
 * @param array $array2
 * @return array
 */
function arrayMergeRecursive($array1, $array2)
{
	foreach ($array2 as $index => $value2) {
		$value1 = isset($array1[$index]) ? $array1[$index] : null;
		$array1[$index] = is_array($value2)
			? arrayMergeRecursive(is_array($value1) ? $value1 : array(), $value2)
			: $value2;
	}
	return $array1;
}

//------------------------------------------------------------------------------- arrayToCollection
function arrayToCollection($array, $class_name)
{
	$collection = array();
	if ($array) {
		reset($array);
		if (!is_numeric(key($array))) {
			$array = arrayFormRevert($array);
		}
		foreach ($array as $key => $element) {
			$collection[$key] = arrayToObject($element, $class_name);
		}
	}
	return $collection;
}

//----------------------------------------------------------------------------------- arrayToObject
function arrayToObject($array, $class_name)
{
	$object = Object_Builder::current()->newInstance($class_name);
	foreach ($array as $property_name => $value) {
		if (($property_name != "id") || !empty($value)) {
			$object->$property_name = $value;
		}
	}
	return $object;
}
