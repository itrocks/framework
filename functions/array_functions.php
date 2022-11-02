<?php

use ITRocks\Framework\Debug\Dead_Or_Alive;

//---------------------------------------------------------------------------------------- arrayCut
/**
 * Cuts a string to create an array, following an array of elements length.
 *
 * @param $string                          string The source string tu cut
 * @param $lengths                         integer[] The length of each element into the string
 * @param $ignore_characters               string|boolean Some characters to ignore. Optional
 * @param $get_trailing_characters_element boolean Gets the trailing characters element if true
 * @return string[] The resulting array
 */
function arrayCut(
	string $string, array $lengths, bool|string $ignore_characters = '',
	bool $get_trailing_characters_element = false
) : array
{
	if (is_bool($ignore_characters)) {
		$get_trailing_characters_element = $ignore_characters;
		$ignore_characters               = '';
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

//------------------------------------------------------------------------------- arrayDiffCombined
/**
 * Return a combined comparative array from array1 to array2
 *
 * @param $array1 array
 * @param $array2 array
 * @param $strict boolean Strict type matching
 * @param $unset  string to display for not-set value (only for destination value and non-strict)
 * @return array
 */
function arrayDiffCombined(
	array $array1, array $array2, bool $strict = false, string $unset = 'UNSET'
) : array
{
	$diff  = $strict
		? arrayDiffRecursive($array1, $array2, true, true)
		: array_diff($array1, $array2);
	$diff2 = $strict
		? arrayDiffRecursive($array2, $array1, true, true)
		: array_diff($array2, $array1);
	foreach ($diff as $key => $element) {
		$diff[$key] = [$element => $diff2[$key] ?? $unset];
	}
	foreach ($diff2 as $key => $element) {
		if (!isset($diff[$key])) {
			$diff[$key] = [$unset => $element];
		}
	}
	return $diff;
}

//------------------------------------------------------------------------------ arrayDiffRecursive
/**
 * @param $array1    array
 * @param $array2    array
 * @param $strict    boolean Strict type matching
 * @param $show_type boolean Return each value type between brackets after each different value
 * @return array|false
 */
function arrayDiffRecursive(
	array $array1, array $array2, bool $strict = false, bool $show_type = false
) : array|bool
{
	$diff = [];
	foreach ($array1 as $key => $value) {
		if (is_object($value)) {
			$value = get_object_vars($value);
		}
		if (!(isset($array2[$key]) || array_key_exists($key, $array2))) {
			$diff[$key] = $value;
			continue;
		}
		$value2 = $array2[$key];
		if (is_object($value2)) {
			$value2 = get_object_vars($value2);
		}
		if (is_array($value)) {
			if (!is_array($value2)) {
				$diff[$key] = $value;
			}
			else {
				$sub_diff = arrayDiffRecursive($value, $value2, $strict, $show_type);
				if ($sub_diff !== false) {
					$diff[$key] = $sub_diff;
				}
			}
		}
		elseif (is_array($value2)) {
			$diff[$key] = $value;
		}
		elseif (($strict && ($value !== $value2)) || (strval($value) !== strval($value2))) {
			$diff[$key] = ($strict && !$show_type) ? $value : strval($value);
			if ($show_type && (gettype($value) !== gettype($value2))) {
				$diff[$key] .= SP . '(' . gettype($value) . ')';
			}
		}
	}
	return $diff ?: false;
}

//--------------------------------------------------------------------------------- arrayFormRevert
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
function arrayFormRevert(mixed $array, bool $case_3 = true) : array
{
	if (!is_array($array)) {
		return $array;
	}
	$result = [];
	foreach ($array as $field_name => $sub_array) {
		if (!is_array($sub_array)) {
			continue;
		}
		foreach ($sub_array as $n => $value) {
			if (!is_array($value)) {
				// case #1
				$result[$n][$field_name] = $value;
				continue;
			}
			foreach ($value as $n2 => $value2) {
				if (is_numeric($n2) || !$case_3) {
					// case #2
					$result[$n][$field_name][$n2] = $value2;
					continue;
				}
				// case #3
				Dead_Or_Alive::isAlive('arrayFormRevert.case3');
				$result[$n2][$field_name][$n] = $value2;
			}
		}
	}
	return $result;
}

//------------------------------------------------------------------------------------- arrayInsert
/**
 * Insert array between keys
 *
 * The inserted sub-array will be inserted after the first found $after_keys, if any is found.
 * If no $after_keys matches, it will be inserted before the first found $before_keys.
 * If no $before_keys matches, it will be appended to the array.
 *
 * @param $array        array
 * @param $array_insert array
 * @param $after_keys   array|string
 * @param $before_keys  array|string
 */
function arrayInsert(
	array &$array, array $array_insert, array|string $after_keys = [], array|string $before_keys = []
) {
	if (!is_array($after_keys))  $after_keys  = [$after_keys];
	if (!is_array($before_keys)) $before_keys = [$before_keys];
	foreach ($after_keys as $key) {
		if (isset($array[$key])) {
			arrayInsertAfter($array, $array_insert, $key);
			return;
		}
	}
	foreach ($before_keys as $key) {
		if (isset($array[$key])) {
			arrayInsertBefore($array, $array_insert, $key);
			return;
		}
	}
	$array = array_merge($array, $array_insert);
}

//-------------------------------------------------------------------------------- arrayInsertAfter
/**
 * Insert array after key
 * if key not exist : insert array to the end
 *
 * @param $array        array
 * @param $array_insert array
 * @param $key          boolean|string
 */
function arrayInsertAfter(array &$array, array $array_insert, bool|string $key = false)
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

//------------------------------------------------------------------------------- arrayInsertBefore
/**
 * Insert array before key
 * if key not exist : insert array to the end
 *
 * @param $array        array
 * @param $array_insert array
 * @param $key          boolean|string
 */
function arrayInsertBefore(array &$array, array $array_insert, bool|string $key = false)
{
	$second_array = [];
	if ($key !== false) {
		$key_position = array_search($key, array_keys($array));
		if ($key_position !== false) {
			$second_array = array_splice($array, $key_position);
		}
	}
	$array = array_merge($array, $array_insert, $second_array);
}

//------------------------------------------------------------------------------- arrayInsertSorted
/**
 * An advanced insert function to insert values into an array, with continuous sort
 *
 * This does not break any pre-done sort
 *
 * @param $array   array to insert the value into
 * @param $value   mixed the inserted value
 * @param $compare callable|string|null objects comparison function, if set
 * @return array $array with the inserted object
 */
function arrayInsertSorted(array $array, mixed $value, callable|string $compare = null) : array
{
	$new_array = [];
	$callable  = $compare ?: function($value1, $value2) { return strcmp($value1, $value2); };
	// copy existing values, and insert the new value at the right place
	$inserted = false;
	foreach ($array as $existing_value) {
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
function arrayIsCallable(array $array) : bool
{
	if (count($array) !== 2) {
		return false;
	}
	$first = reset($array);
	$last  = end($array);
	return (is_object($first) || class_exists($first)) && method_exists($first, $last);
}

//----------------------------------------------------------------------------- arrayKeysAllNumeric
/**
 * @param $array   array
 * @param $recurse boolean
 * @return boolean
 */
function arrayKeysAllNumeric(array $array, bool $recurse = false) : bool
{
	foreach ($array as $key => $value) {
		if (
			!is_numeric($key)
			|| ($recurse && is_array($value) && !arrayKeysAllNumeric($value, $recurse))
		) {
			return false;
		}
	}
	return true;
}

//----------------------------------------------------------------------------- arrayMergeRecursive
/**
 * Merges two arrays, with recursion
 *
 * Elements of $array1 and $array2 with same index (even if numeric) :
 * $array2 element replaces $array1 element.
 * If $array2 element is an array : merge $array1 and $array2 array element, recursively.
 *
 * @param $array1 array
 * @param $array2 array
 * @param $clear  string|null You can tell a value that clears every data from $array1 before merge
 * @return array
 */
function arrayMergeRecursive(array $array1, array $array2, string $clear = null) : array
{
	foreach ($array2 as $index => $value2) {
		if ($clear && ($value2 === $clear)) {
			$array1 = null;
			unset($array2[$index]);
			continue;
		}
		$value1 = $array1[$index] ?? null;
		if (is_numeric($index) && !is_array($value1) && !is_array($value2)) {
			if (!in_array($value2, $array1)) {
				$array1[] = $value2;
			}
			continue;
		}
		if (is_array($value2)) {
			$value2         = arrayMergeRecursive(is_array($value1) ? $value1 : [], $value2, $clear);
			$array1[$index] = $value2;
			continue;
		}
		$array1[$index] = $value2;
	}
	return $array1;
}

//-------------------------------------------------------------------------------- arrayNamedValues
/**
 * Returns only values which key is not numeric
 *
 * @param $array array
 * @return array
 */
function arrayNamedValues(array $array) : array
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
 * @param $array array|mixed array or element (will be changed into array, always)
 * @param $keys  integer[]|string[]|null[] multidimensional array keys
 * @param $init  mixed initial / default value
 */
function arraySet(mixed &$array, array $keys, mixed $init)
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
 * @param $array array|float|int
 * @return float|int
 */
function arraySumRecursive(array|float|int $array) : float|int
{
	if (is_array($array)) {
		$sum = .0;
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
 * @return array
 */
function arrayToTree(array $array, bool $recurse = true) : array
{
	$result     = [];
	$sub_arrays = [];
	foreach ($array as $key => $value) {
		if (str_contains($key, DOT)) {
			[$super_key, $sub_key]            = explode(DOT, $key, 2);
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
 * @return array
 */
function arrayUnnamedValues(array $array) : array
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
 * @example
 * explodeStringInArrayToDoubleArray(SP, [['Dot a'], ['the cat runs'])
 * returns [['Dot', 'a'], ['the', 'cat', 'runs']]
 * @param $delimiter string The boundary string
 * @param $array     array The input array, can be an array of string or an array of array of string
 * @return array Return an array of array of string
 */
function explodeStringInArrayToDoubleArray(string $delimiter, array $array) : array
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
function explodeStringInArrayToSimpleArray(string $delimiter, array $array) : array
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
function objectInsertSorted(array $array, object $object, array|callable|string $compare) : array
{
	$new_array = [];
	/** @var $callable callable The callable function adapted to $compare */
	if (is_callable($compare) || (is_array($compare) && arrayIsCallable($compare))) {
		$callable = $compare;
	}
	elseif (is_string($compare)) {
		$callable = function ($object1, $object2) use ($compare) {
			return strcmp($object1->$compare, $object2->$compare);
		};
	}
	else {
		$callable = function ($object1, $object2) use ($compare) {
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
function objectToArray(array|object $object, $get_private = false) : array
{
	if (is_object($object)) {
		if (isset($object->__objectToArray)) {
			$object = '...';
		}
		else {
			$protected_object = $object;
			$object           = $get_private ? ((array)$object) : get_object_vars($object);
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
 * @return array
 */
function treeToArray(array $array, string $ignore_key = '') : array
{
	$result = [];
	foreach ($array as $key => $val) {
		if (is_array($val)) {
			foreach (treeToArray($val, $ignore_key) as $sub_key => $sub_val) {
				$result[$key . ((strval($sub_key) === $ignore_key) ? '' : (DOT . $sub_key))] = $sub_val;
			}
		}
		else {
			$result[$key] = $val;
		}
	}
	return $result;
}

//------------------------------------------------------------------------------- unsetKeyRecursive
/**
 * Remove elements of the $array or its sub-arrays, which key match any value of $keys
 *
 * @param $array             array
 * @param $keys              integer|integer[]|string|string[]
 * @param $replacement_value mixed The value of the element is replaced by this one instead of unset
 */
function unsetKeyRecursive(array &$array, array|int|string $keys, mixed $replacement_value = null)
{
	if (!is_array($keys)) {
		$keys = [$keys];
	}
	foreach ($array as $key => $element) {
		if (in_array($key, $keys)) {
			if (isset($replacement_value)) {
				$array[$key] = $replacement_value;
			}
			else {
				unset($array[$key]);
			}
		}
		elseif (is_array($element)) {
			unsetKeyRecursive($element, $keys);
		}
	}
}
