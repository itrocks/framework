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
 * @param $strict    boolean Strict type matching
 * @param $show_type boolean Return each value type between brackets after each different value
 * @return array|boolean
 */
function arrayDiffRecursive(array $array1, array $array2, $strict = false, $show_type = false)
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
				$sub_diff = arrayDiffRecursive($value, $array2[$key], $strict, $show_type);
				if ($sub_diff !== false) {
					$diff[$key] = $sub_diff;
				}
			}
		}
		elseif (is_array($array2[$key])) {
			$diff[$key] = $value;
		}
		elseif (($strict && ($value !== $array2[$key])) || (strval($value) !== strval($array2[$key]))) {
			$diff[$key] = strval($value);
			if ($show_type && (gettype($value) !== gettype($array2[$key]))) {
				$diff[$key] .= SP . '(' . gettype($value) . ')';
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

//-------------------------------------------------------------------------------- arrayInsertAfter
/**
 * Insert array after key
 * if key not exist : insert array to the end
 *
 * @param $array        array
 * @param $array_insert array
 * @param $key          string|boolean
 */
function arrayInsertAfter(array &$array, array $array_insert, $key = false)
{
	$second_array = [];
	if ($key !== false) {
		$key_position = array_search($key, array_keys($array));
		if ($key_position !== false) {
			$second_array = array_splice($array, $key_position + 1);
		}
	}
	$array = array_merge($array, $array_insert, $second_array);
}

//------------------------------------------------------------------------------ objectInsertSorted
/**
 * An advanced insert function to insert values into an array, with continuous sort
 *
 * This does not break any pre-done sort
 *
 * @param $array   array to insert the value into
 * @param $value   mixed the inserted value
 * @param $compare callable|string objects comparison function, if set
 * @return array $array with the inserted object
 */
function arrayInsertSorted($array, $value, $compare = null)
{
	$new_array = [];
	$callable  = $compare ?: function($value1, $value2) { return strcmp($value1, $value2); };
	// copy existing values, and insert the new value at the right place
	$inserted = false;
	foreach ($array as $key => $existing_value) {
		// insert the new value before the existing value
		if (!$inserted && ($callable($existing_value, $value) > 0)) {
			$new_array[] = $value;
			$inserted    = true;
		}
		// insert the exiting value
		$new_array[] = $existing_value;
	}
	// append the new value at the end, if not already inserted (last chance)
	if (!$inserted) {
		$new_array[] = $value;
	}
	return $new_array;
}

//--------------------------------------------------------------------------------- arrayIsCallable
/**
 * Returns true if $array represents a callable :
 * - two elements
 * - first comes a valid name of a class (will be auto-loaded)
 * - next comes an existing method name
 *
 * @param $array array
 * @return boolean
 */
function arrayIsCallable(array $array)
{
	if (count($array) !== 2) {
		return false;
	}
	$first = reset($array);
	$last  = end($array);
	return (is_object($first) || class_exists($first)) && method_exists($first, $last);
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

//---------------------------------------------------------------------------------------- arraySet
/**
 * Create missing elements into the different dimensions of the array if they do not exist
 * If they exist : do nothing
 *
 * @example arraySet($array, [1, 2, 3], null);
 * will ensure that $array[1][2][3] is set,
 * and will initialize its value to null if not
 * @param $array array
 * @param $keys  integer[]|string[]|null[] multidimensional array keys
 * @param $init  mixed initial / default value
 */
function arraySet(&$array, array $keys, $init)
{
	$has_element = true;
	$where       =& $array;
	if (!is_array($where)) {
		$where = [];
	}
	foreach ($keys as $key) {
		if (!isset($where[$key])) {
			$where[$key] = [];
			$has_element = false;
		}
		$where =& $where[$key];
	}
	if (!$has_element) {
		$where = $init;
	}
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
	return floatval($array);
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

//--------------------------------------------------------------- explodeStringInArrayToDoubleArray
/**
 * Explodes strings in array or in array of array, and return an array of array of string.
 *
 * @example
 * explodeStringInArrayToDoubleArray(SP, ['Dot', 'a cat', 'the cat runs'])
 * returns [['Dot'], ['a', 'cat'], ['the', 'cat', 'runs']]
 *
 * @example
 * explodeStringInArrayToDoubleArray(SP, [['Dot a'], ['the cat runs'])
 * returns [['Dot', 'a'], ['the', 'cat', 'runs']]
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
			$tab[]   = empty($explode) ? [$element] : $explode;
		}
	}
	return $tab;
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

//------------------------------------------------------------------------------ objectInsertSorted
/**
 * An advanced insert function to insert values into an array, with continuous sort
 *
 * - $array : the source array. sorting is by objects, if the array contains anything else than
 *   objects, the non-object values are ignored and the insert is immediately before the next object
 *
 * @param $array   object[]|array to insert the object into
 * @param $object  object the inserted object
 * @param $compare callable|string|string[] objects comparison function or property(ies)
 * @return array $array with the inserted object
 */
function objectInsertSorted($array, $object, $compare)
{
	$new_array = [];
	/** @var $callable callable The callable function adapted to $compare */
	if (is_callable($compare) || (is_array($compare) && arrayIsCallable($compare))) {
		$callable = $compare;
	}
	elseif (is_string($compare)) {
		$callable = function($object1, $object2) use ($compare) {
			return strcmp($object1->$compare, $object2->$compare);
		};
	}
	else {
		$callable = function($object1, $object2) use ($compare) {
			foreach ($compare as $property_name) {
				$comparison = strcmp($object1->$property_name, $object2->$property_name);
				if ($comparison) {
					return $comparison;
				}
			}
			return 0;
		};
	}
	// search the key of the last object in the list
	$last_object = end($array);
	while (($last_object !== false) && !is_object($last_object)) {
		$last_object = prev($array);
	}
	// copy existing objects, and insert the new object at the right place
	$inserted = false;
	$last_key = key($array);
	foreach ($array as $key => $existing_object) {
		// insert the new object before the existing object
		if (!$inserted && is_object($existing_object) && ($callable($existing_object, $object) > 0)) {
			$new_array[] = $object;
			$inserted    = true;
		}
		// insert the exiting object
		$new_array[] = $existing_object;
		// insert the new object immediately after the last existing object (not after strings)
		if (!$inserted && ($key === $last_key)) {
			$new_array[] = $object;
			$inserted    = true;
		}
	}
	// append the new object at the end, if not already inserted (last chance)
	if (!$inserted) {
		$new_array[] = $object;
	}
	return $new_array;
}

//----------------------------------------------------------------------------------- objectToArray
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
