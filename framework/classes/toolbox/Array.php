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
	foreach ($array2 as $index => $value) {
		$array1[$index] = is_array($value)
			? arrayMergeRecursive($array1[$index], $value)
			: $value;
	}
	return $array1;
}
