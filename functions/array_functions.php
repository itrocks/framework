<?php

use ITRocks\Framework\Debug\Dead_Or_Alive;

/**
 * Cuts a string to create an array, following an array of elements length.
 *
 * @param $string            string The source string tu cut
 * @param $lengths           integer[] The length of each element into the string
 * @param $ignore_characters string|boolean Some characters to ignore. Optional.
 * @param $get_trailing_characters_element boolean Gets the trailing characters element if true
 * @return string[] The resulting array
 */
function arrayCut(
	$string, array $lengths, $ignore_characters = '', $get_trailing_characters_element = false
) {
	if (is_bool($ignore_characters)) {
		$get_trailing_characters_element = $ignore_characters;
		$ignore_characters = '';
	}
	if (strlen($ignore_characters)) {
		$string = str_replace(str_split($ignore_characters), '', $string);
	}
	$string_length = strlen($string);
	$position      = 0;
	$result        = [];
	foreach ($lengths as $length) {
		$result[] = substr($string, $position, $length);
		$position += $length;
		if ($position >= $string_length) {
			break;
		}
	}
	if ($get_trailing_characters_element && ($position < $string_length)) {
		$result[] = substr($string, $position);
	}
	return $result;
}

//------------------------------------------------------------------------------ arrayDiffRecursive
/**
 * @param $array1    array
 * @param $array2    array
 * @param $show_type boolean
 * @return array|boolean
 */
function arrayDiffRecursive(array $array1, array $array2, $show_type = false)
{
	$diff = [];
	foreach ($array1 as $key => $value) {
		if (!(isset($array2[$key]) || array_key_exists($key, $array2))) {
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
			$diff[$key] = strval($value);
			if ($show_type && (gettype($value) !== gettype($array2[$key]))) {
				$diff[$key] .= '(' . gettype($value) . ')';
			}
		}
	}
	return $diff ? $diff : false;
}

//------------------------------------------------------------------------ function arrayFormRevert
/**
 * Reverts an array coming from a dynamic form result
 *
 * @example #1
 * Source array is [$field_name => [$n => $value]]
 * Destination array is [$n => [$field_name => $value]]
 * @example #2
 * Source array is [$field_name => [$n => [$n2 => $value]]
 * Destination array is [$n => [$field_name => [$n2 => $value]]
 * @example #3
 * Source array is [$field_name => [$sub_field_name => [$n => $value]]
 * Destination array is [$n => [$field_name => [$sub_field_name => $value]]
 * @example #3 with $case_3 = false
 * Source array is [$field_name => [$n => [$sub_field_name => $value]]
 * Destination array is [$n => [$field_name => [$sub_field_name => $value]]
 * @param $array  mixed array or element
 * @param $case_3 boolean
 * @return array
 */
function arrayFormRevert($array, $case_3 = true)
{
	if (is_array($array)) {
		$result = [];
		foreach ($array as $field_name => $sub_array) {
			if (is_array($sub_array)) {
				foreach ($sub_array as $n => $value) {
					if (!is_array($value)) {
						// case #1
						$result[$n][$field_name] = $value;
					}
					else {
						foreach ($value as $n2 => $value2) {
							if (is_numeric($n2) || !$case_3) {
								// case #2
								$result[$n][$field_name][$n2] = $value2;
							}
							else {
								// case #3
								Dead_Or_Alive::isAlive('arrayFormRevert.case3');
								$result[$n2][$field_name][$n] = $value2;
							}
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
function arrayMergeRecursive(array $array1, array $array2)
{
	foreach ($array2 as $index => $value2) {
		if (($index === ':') && ($value2 === 'clear')) {
			$array1 = null;
			unset($array2[$index]);
		}
		else {
			$value1 = isset($array1[$index]) ? $array1[$index] : null;
			if (is_numeric($index) && !is_array($value1) && !is_array($value2)) {
				if (!in_array($value2, $array1)) {
					$array1[] = $value2;
				}
			}
			elseif (is_array($value2)) {
				$value2 = arrayMergeRecursive(is_array($value1) ? $value1 : [], $value2);
				if (isset($value2)) {
					$array1[$index] = $value2;
				}
				else {
					unset($array1[$index]);
				}
			}
			else {
				$array1[$index] = $value2;
			}
		}
	}
	return $array1;
}

//-------------------------------------------------------------------------------- arrayNamedValues
/**
 * Returns only values which key is not numeric
 *
 * @param $array array
 * @return mixed[]
 */
function arrayNamedValues(array $array)
{
	$result = [];
	foreach ($array as $key => $value) {
		if (!is_numeric($key)) {
			$result[$key] = $value;
		}
	}
	return $result;
}

//------------------------------------------------------------------------------- arraySumRecursive
/**
 * Returns the sum of all elements into a recursive (aka multidimensional) array
 *
 * @param $array array|number
 * @return number
 */
function arraySumRecursive($array)
{
	if (is_array($array)) {
		$sum = 0;
		foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $value) {
			$sum += $value;
		}
		return $sum;
	}
	return $array + 0;
}

//------------------------------------------------------------------------------------- arrayToTree
/**
 * Change an array with 'key.sub_key.final_key' keys into a tree with[key][sub_key][final_key]
 *
 * @param $array   array
 * @param $recurse boolean
 * @return mixed[]
 */
function arrayToTree(array $array, $recurse = true)
{
	$result     = [];
	$sub_arrays = [];
	foreach ($array as $key => $value) {
		if (strpos($key, DOT) !== false) {
			list($super_key, $sub_key) = explode(DOT, $key, 2);
			$sub_arrays[$super_key][$sub_key] = $value;
		}
		else {
			$result[$key] = $value;
		}
	}
	foreach ($sub_arrays as $key => $sub_array) {
		$result[$key] = $recurse ? arrayToTree($sub_array) : $sub_array;
	}
	return $result;
}

//------------------------------------------------------------------------------ arrayUnnamedValues
/**
 * Returns only values which key is numeric
 *
 * @param $array array
 * @return mixed[]
 */
function arrayUnnamedValues(array $array)
{
	$result = [];
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
 * Keys are cumulated to a single 'key.sub_key.final_key' key name
 *
 * @param $array      array
 * @param $ignore_key string if set, this key is ignored and set as the 'main' value of a node
 * @return mixed[]
 */
function treeToArray(array $array, $ignore_key = null)
{
	$result = [];
	foreach ($array as $key => $val) {
		if (is_array($val)) {
			foreach (treeToArray($val, $ignore_key) as $sub_key => $sub_val) {
				$result[$key . ((strval($sub_key) === strval($ignore_key)) ? '' : (DOT . $sub_key))]
					= $sub_val;
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
 * Explode strings in array and return a larger array
 *
 * @example explodeStringInArrayToSimpleArray(' ', ['Dot', 'a cat', 'the cat run'))
 *          returns : ['Dot', 'a', 'cat', 'the', 'cat', 'run')
 * @param $delimiter string The boundary string.
 * @param $array     array The input array.
 * @return array Return a larger array explode by delimiter.
 */
function explodeStringInArrayToSimpleArray($delimiter, array $array)
{
	$tab = [];
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
 * explodeStringInArrayToDoubleArray(' ', ['Dot', 'a cat', 'the cat run'))
 * return : [['Dot'), ['a', 'cat'), ['the', 'cat', 'run'))
 *
 * @example
 * explodeStringInArrayToDoubleArray(' ', [['Dot a'), ['the cat run'))
 * return : [['Dot', 'a'), ['the', 'cat', 'run'))
 *
 * @param $delimiter string The boundary string.
 * @param $array     array The input array, can be an array of string or an array of array of string.
 * @return array Return an array of array of string.
 */
function explodeStringInArrayToDoubleArray($delimiter, array $array)
{
	$tab = [];
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
				$tab[] = [$element];
			}
		}
	}
	return $tab;
}

/**
 * @param $object      array|object
 * @param $get_private boolean if
 * @return array
 */
function objectToArray($object, $get_private = false)
{
	if (is_object($object)) {
		if (isset($object->__objectToArray)) {
			$object = '...';
		}
		else {
			$protected_object = $object;
			$object = $get_private ? ((array)$object) : get_object_vars($object);
			$protected_object->__objectToArray = true;
		}
	}
	if (is_array($object)) {
		if (isset($object['__objectToArray'])) {
			$object = '...';
		}
		else {
			$object['__objectToArray'] = true;
			foreach ($object as $key => $value) {
				if (is_array($value) || is_object($value)) {
					$object[$key] = objectToArray($value, $get_private);
				}
			}
			unset($object['__objectToArray']);
		}
	}
	if (isset($protected_object)) {
		unset($protected_object->__objectToArray);
	}
	return $object;
}
