<?php

//----------------------------------------------------------------------------------------- backSet
/**
 * Sets a new temporary value into a variable, and returns the old value
 *
 * @example
 * // backup the old value and store a temporary value :
 * $old_value = backSet($variable, $temporary_value);
 * // do things with the temporary value into $variable here (...) ; reset to the original value :
 * $variable = $old_value
 * @param $variable mixed
 * @param $value    mixed
 * @return mixed
 */
function backSet(mixed &$variable, mixed $value) : mixed
{
	$old_value = $variable;
	$variable  = $value;
	return $old_value;
}

//-------------------------------------------------------------------------------------------- swap
/**
 * Swaps the values of two variables
 *
 * @param $variable1 mixed
 * @param $variable2 mixed
 */
function swap(mixed &$variable1, mixed &$variable2)
{
	$temporary = $variable1;
	$variable1 = $variable2;
	$variable2 = $temporary;
}
