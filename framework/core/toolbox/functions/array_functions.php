<?php

//------------------------------------------------------------------------------ arrayDiffRecursive
/**
 * @param $array1    array
 * @param $array2    array
 * @param $show_type boolean
 * @return array|boolean
 */
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
 * @param $array array
 * @return array
 */
function arrayFormRevert($array)
{
	if (is_array($array)) {
		$result = array();
		foreach ($array as $field_name => $sub_array) {
			if (is_array($sub_array)) {
				foreach ($sub_array as $n => $value) {
					if (!is_array($value)) {
						$result[$n][$field_name] = $value;
					}
					else {
						foreach ($value as $n2 => $value2) {
							$result[$n2][$field_name][$n] = $value2;
						}
					}
				}
			}
		}
		return $result;
	}
	else {
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
 * @param $array1 array
 * @param $array2 array
 * @return array
 */
function arrayMergeRecursive($array1, $array2)
{
	foreach ($array2 as $index => $value2) {
		$value1 = isset($array1[$index]) ? $array1[$index] : null;
		if (is_numeric($index) && !is_array($value1) && !is_array($value2)) {
			if (!in_array($value2, $array1)) {
				$array1[] = $value2;
			}
		}
		else {
			$array1[$index] = is_array($value2)
				? arrayMergeRecursive(is_array($value1) ? $value1 : array(), $value2)
				: $value2;
		}
	}
	return $array1;
}

//-------------------------------------------------------------------------------- arrayNamedValues
/**
 * Returns only values which key is not numeric
 *
 * @param $array mixed[]
 * @return mixed[]
 */
function arrayNamedValues($array)
{
	$result = array();
	foreach ($array as $key => $value) {
		if (!is_numeric($key)) {
			$result[$key] = $value;
		}
	}
	return $result;
}

//------------------------------------------------------------------------------ arrayUnnamedValues
/**
 * Returns only values which key is numeric
 *
 * @param $array mixed[]
 * @return mixed[]
 */
function arrayUnnamedValues($array)
{
	$result = array();
	foreach ($array as $key => $value) {
		if (is_numeric($key)) {
			$result[$key] = $value;
		}
	}
	return $result;
}

//------------------------------------------------------------------------------------- treeToArray
/**
 * Linearize a tree to an array
 *
 * Keys are cumulated to a single "key.sub_key.final_key" key name
 *
 * @param $array      mixed[]
 * @param $ignore_key string if set, this key is ignored and set as the "main" value of a node
 * @return mixed[]
 */
function treeToArray($array, $ignore_key = null)
{
	$result = array();
	foreach ($array as $key => $val) {
		if (is_array($val)) {
			foreach (treeToArray($val, $ignore_key) as $sub_key => $sub_val) {
				$result[$key . (("$sub_key" === "$ignore_key") ? "" : ("." . $sub_key))] = $sub_val;
			}
		}
		else {
			$result[$key] = $val;
		}
	}
	return $result;
}

//--------------------------------------------------------------- explodeStringInArrayToSimpleArray
/**
 * Explode strings in array and return a larger array.
 * @param $delimiter string The boundary string.
 * @param $array array The input array.
 * @return array Return a larger array explode by delimiter.
 * @example explodeStringInArrayToSimpleArray(" ", array("Dot", "a cat", "the cat run"))
 * return : array("Dot", "a", "cat", "the", "cat", "run")
 */
function explodeStringInArrayToSimpleArray($delimiter, $array)
{
	$tab = array();
	foreach ($array as $element) {
		$explode = explode($delimiter, $element);
		if (!empty($explode)) {
			foreach ($explode as $part) {
				$tab[] = $part;
			}
		}
		else {
			$tab[] = $element;
		}
	}
	return $tab;
}

//--------------------------------------------------------------- explodeStringInArrayToDoubleArray
/**
 * Explodes strings in array or in array of array, and return an array of array of string.
 *
 * @example
 * explodeStringInArrayToDoubleArray(" ", array("Dot", "a cat", "the cat run"))
 * return : array(array("Dot"), array("a", "cat"), array("the", "cat", "run"))
 *
 * @example
 * explodeStringInArrayToDoubleArray(" ", array(array("Dot a"), array("the cat run"))
 * return : array(array("Dot", "a"), array("the", "cat", "run"))
 *
 * @param $delimiter string The boundary string.
 * @param $array     array The input array, can be an array of string or an array of array of string.
 * @return array Return an array of array of string.
 */
function explodeStringInArrayToDoubleArray($delimiter, $array)
{
	$tab = array();
	foreach ($array as $element) {
		if (is_array($element)) {
			$tab[] = explodeStringInArrayToDoubleArray($delimiter, $element);
		}
		else {
			$explode = explode($delimiter, $element);
			if (!empty($explode)) {
				$tab[] = $explode;
			}
			else {
				$tab[] = array($element);
			}
		}
	}
	return $tab;
}
