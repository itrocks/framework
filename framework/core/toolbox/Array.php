<?php

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
