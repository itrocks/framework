<?php

//---------------------------------------------------------------------------------- pregMatchArray
/**
 * Checks if a string matches any regular expressions into an array
 *
 * @example
 * pregMatchArray(['a.*y', 'another'], 'abbey') will return true because 'abbey' matches 'a.*y'
 * eq. pregMatchArray(['^a.*y$', '^another$'], 'abbey', false)
 * eq. pregMatchArray(['~^a.*y$~', '~^another$~'], 'abbey', false, false)
 * pregMatchArray(['~a.*y~', '~another~'], 'abbey', true, false) is not supported !
 * @param $haystack  array the array containing supported regular expressions to search into
 * @param $needle    string the string to search into
 * @param $complete  boolean if false, the search is "contains" and not "is
 * @param $delimiter string implicit delimiter. Default is ~
 * @return boolean true if $needle matches any of regular expressions into $haystack
 */
function pregMatchArray(
	array $haystack, string $needle, bool $complete = true, string $delimiter = '~'
) : bool
{
	if ($complete && !strlen($delimiter)) {
		trigger_error('$delimiter must be set if $complete is true', E_USER_ERROR);
	}
	list($start, $stop) = $complete ? ['^', '$'] : ['', ''];
	if (strlen($delimiter)) {
		$start = $delimiter . $start;
		$stop .= $delimiter;
	}
	foreach ($haystack as $test) {
		if (preg_match($start . $test . $stop, $needle)) {
			return true;
		}
	}
	return false;
}
