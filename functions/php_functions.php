<?php

//------------------------------------------------------------------------------ upgradeMemoryLimit
/**
 * Upgrade memory limit.
 * If $memory_limit is lower than memory_limit, will keep memory_limit.
 *
 * @param $memory_limit string php memory limit in o / given unit
 *                      (eg '4294967296', '4096M', '4G'). 0 / -1 : no limit
 * @return boolean false if the new memory limit was a downgrade of the actual one (nothing done)
 */
function upgradeMemoryLimit(string $memory_limit) : bool
{
	$old_memory_limit = ini_get('memory_limit');
	$m = ['G' => 1024 * 1024 * 1024, 'M' => 1024 * 1024, 'K' => 1024];
	// convert $memory_limit to octets
	$unit = substr($memory_limit, -1);
	$memory_limit = isset($m[$unit])
		? intval(substr($memory_limit, 0, -1)) * $m[$unit]
		: intval($memory_limit);
	// convert old_memory_limit to octets
	$unit = intval(substr($old_memory_limit, -1));
	$old_memory_limit = isset($m[$unit])
		? intval(substr($old_memory_limit, 0, -1)) * $m[$unit]
		: intval($old_memory_limit);
	// upgrade (round to the ceil MB)
	if ($memory_limit <= 0) {
		$memory_limit = -1;
	}
	if ((($old_memory_limit > 0) && ($memory_limit > $old_memory_limit)) || ($memory_limit === -1)) {
		ini_set('memory_limit', ceil($memory_limit / 1024 / 1024) . 'M');
		return true;
	}
	return false;
}

//-------------------------------------------------------------------------------- upgradeTimeLimit
/**
 * Upgrade time limit.
 * If $time_limit is lower than max_execution_time, will keep max_execution_time.
 *
 * @param $time_limit integer php execution time limit in seconds (0 / -1 : no limit)
 * @return boolean false if the new time limit was a downgrade of the actual one (nothing done)
 */
function upgradeTimeLimit(int $time_limit) : bool
{
	if ($time_limit <= 0) {
		$time_limit = 0;
	}
	if (
		(($time_limit > 0) && ($time_limit > ini_get('max_execution_time'))) || !$time_limit
	) {
		ini_set('max_execution_time', $time_limit);
		set_time_limit($time_limit);
		return true;
	}
	return false;
}
