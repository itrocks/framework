<?php
namespace SAF\Framework;

//-------------------------------------------------------------------- function arrayMergeRecursive
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
