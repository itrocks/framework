<?php

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
function backSet(&$variable, $value)
{
	$old_value = $variable;
	$variable  = $value;
	return $old_value;
}
