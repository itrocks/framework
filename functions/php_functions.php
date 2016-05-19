<?php

/**
 * Upgrade time limit.
 * If $time_limit is lower than max_execution_time, will keep max_execution_time.
 *
 * @param $time_limit integer php execution time limit in secondes
 */
function upgradeTimeLimit($time_limit)
{
	$time_limit = max(ini_get('max_execution_time'), $time_limit);
	ini_set('max_execution_time', $time_limit);
	set_time_limit($time_limit);
}
