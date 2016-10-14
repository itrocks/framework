#!/usr/bin/php
<?php
/**
 * Call this script from command line / scheduled tasks to call features from the software
 * - Will be considered like running under HTTPS
 * - Remote address will be 'console'
 */
error_reporting(E_ALL);

if (empty($argv[1])) {
	$argv[1] = '/';
}

// check if the feature is already running
exec("ps -aux | grep $argv[1] | grep -v grep", $outputs);
$count  = 0;
$output = [];
foreach ($outputs as $output) {
	if (strpos($output, $argv[1]) && strpos($output, '/usr/bin/php')) {
		$count++;
	}
}

if ($count > 1) {
	echo "Already running $argv[1]\n";
	print_r($outputs);
}
else {

	// store the "running" file into /home/tmp, if exists, or into the project's tmp dir
	$tmp_dir = file_exists('/home/tmp') ? '/home/tmp' : (__DIR__ . '/../../tmp');
	if (!file_exists($tmp_dir)) {
		mkdir($tmp_dir, 0777, true);
		chmod($tmp_dir, 0777);
	}
	$running_file = $tmp_dir . '/' . (str_replace('/', '_', substr($argv[1], 1)) ?: 'index');
	touch($running_file);

	// cleanup global scope
	unset($count);
	unset($output);
	unset($outputs);
	unset($tmp_dir);

	// parse parameters
	if (empty($_GET)) {
		$_GET = ['as_widget' => true];
		foreach ($argv as $k => $v) {
			if ($k > 1) {
				list($k, $v) = explode('=', $v, 2);
				$_GET[$k] = $v;
			}
		}
		$_SERVER['HTTPS']       = true;
		$_SERVER['PATH_INFO']   = $argv[1];
		$_SERVER['REMOTE_ADDR'] = 'console';
		$_SERVER['SCRIPT_NAME'] = '/console.php';

		// wait for unlock
		while (is_file('lock-console')) {
			usleep(100000);
			clearstatcache(true, 'lock-console');
		}

		// execute
		require __DIR__ . '/index.php';
	}

	@unlink($running_file);
}
