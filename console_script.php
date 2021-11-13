<?php
namespace ITRocks\Framework;

/**
 * Call with itrocks/framework/console
 *
 * Call this script from command line / scheduled tasks to call features from the software
 * - Will be considered like running under HTTPS
 * - Remote address will be 'console'
 */

include_once __DIR__ . '/Console.php';

error_reporting(E_ALL);

chdir(__DIR__ . '/../..');
Console::$current = new Console($argv ?? ['/', '-g', 'X']);
if (Console::$current->prepare()) {
	include_once __DIR__ . '/index.php';
	Console::$current->end();
}
