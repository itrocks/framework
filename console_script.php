<?php
namespace ITRocks\Framework;

/**
 * Call with itrocks/framework/console
 *
 * Call this script from command line / scheduled tasks to call features from the software
 * - Will be considered like running under HTTPS
 * - Remote address will be 'console'
 */

error_reporting(E_ALL);

chdir(__DIR__ . '/../..');

function iniSetErrorLog()
{
	if (!file_exists('loc.php')) {
		return;
	}
	$loc_content        = file_get_contents('loc.php');
	$error_log_position = strpos($loc_content, '#error_log: ');
	if (!$error_log_position) {
		return;
	}
	$error_log_position += 12;
	$error_log_stop = strpos($loc_content, "\n", $error_log_position) ?: strlen($loc_content);
	$error_log = trim(substr($loc_content, $error_log_position, $error_log_stop - $error_log_position));
	if (!$error_log) {
		return;
	}
	$error_log_directory = pathinfo($error_log,  PATHINFO_DIRNAME);
	if (!is_dir($error_log_directory)) {
		exec('mkdir -p "' . $error_log_directory . '"');
		clearstatcache();
		if (!is_dir($error_log_directory)) {
			trigger_error("Could not create error_log directory $error_log_directory", E_USER_ERROR);
		}
	}
	ini_set('error_log', $error_log);
}
iniSetErrorLog();

include_once __DIR__ . '/Console.php';
Console::$current = new Console($argv ?? ['/', '-g', 'X']);
if (Console::$current->prepare()) {
	include_once __DIR__ . '/index.php';
	Console::$current->end();
}
