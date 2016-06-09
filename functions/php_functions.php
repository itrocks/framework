<?php

/**
 * Upgrade memory limit.
 * If $memory_limit is lower than memory_limit, will keep memory_limit.
 *
 * @param $memory_limit string php memory limit in o / given unit (eg '4294967296', '4096M', '4G')
 */
function upgradeMemoryLimit($memory_limit)
{
	$old_memory_limit = ini_get('memory_limit');
	$m = ['G' => 1024 * 1024 * 1024, 'M' => 1024 * 1024, 'K' => 1024];
	// convert $memory_limit to octets
	$unit = substr($memory_limit, -1);
	if (isset($m[$unit])) {
		$memory_limit *= $m[$unit];
	}
	// convert old_memory_limit to octets
	$unit = substr($old_memory_limit, -1);
	if (isset($m[$unit])) {
		$old_memory_limit *= $m[$unit];
	}
	// upgrade (round to the ceil Mo)
	if ($memory_limit > $old_memory_limit) {
		ini_set('memory_limit', ceil($memory_limit / 1024 / 1024) . 'M');
	}
}

/**
 * Upgrade time limit.
 * If $time_limit is lower than max_execution_time, will keep max_execution_time.
 *
 * @param $time_limit integer php execution time limit in seconds
 */
function upgradeTimeLimit($time_limit)
{
	if ($time_limit > ini_get('max_execution_time')) {
		ini_set('max_execution_time', $time_limit);
		set_time_limit($time_limit);
	}
}
