<?php
namespace ITRocks\Framework;

use ITRocks\Framework\AOP\Include_Filter;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Plugin\Manager;
use ITRocks\Framework\Tools\Paths;

// php settings
chdir(__DIR__ . '/../..');
error_reporting(E_ALL);
ini_set( 'arg_separator.output',            '&amp;' );
ini_set( 'default_charset',                 'UTF-8' );
ini_set( 'max_execution_time',              30      );
ini_set( 'max_input_vars',                  1000000 );
ini_set( 'memory_limit',                    '2G'    );
ini_set( 'session.use_cookies',             true    );
ini_set( 'xdebug.max_nesting_level',        255     );
ini_set( 'xdebug.var_display_max_children', 10      );
ini_set( 'xdebug.var_display_max_data',     150     );
ini_set( 'xdebug.var_display_max_depth',    3       );
mysqli_report(MYSQLI_REPORT_OFF);
putenv('LANG=fr_FR.UTF-8');
set_time_limit(30);

// constants immediately available
include_once __DIR__ . '/functions/constants.php';
include_once __DIR__ . '/functions/http_functions.php';
cors();

// enable running without PATH_INFO
if (!isset($_SERVER['PATH_INFO'])) {
	$_SERVER['PATH_INFO'] = SL;
}

// wait for unlock
while (is_file('lock')) {
	usleep(100000);
	clearstatcache(true, 'lock');
}

// activate paths
include_once __DIR__ . '/tools/Paths.php';
Paths::register();
// enable cache files for compiled scripts : includes must all use this filter
include_once __DIR__ . '/aop/include_filter/Include_Filter.php';
Include_Filter::register();
// enable autoloader
/** @noinspection PhpUnhandledExceptionInspection valid file */
include_once Include_Filter::file(__DIR__ . '/Autoloader.php');
(new Autoloader)->register();

// run main controller
/** @noinspection PhpUnhandledExceptionInspection may throw a global Include_Filter\Exception */
echo (new Main)
	->init()
	->addTopCorePlugins([
		new Manager,
		new Html_Session(['use_cookie' => true])
	])
	->run($_SERVER['PATH_INFO'], $_GET, $_POST, $_FILES);

// Display result on client browser now, as session serialization could take a moment
flush();

// When running php on cgi mode, getcwd() will return '/usr/lib/cgi-bin' on specific serialize()
// calls. This is a php bug, calling session_write_close() here will serialize session variables
// within the correct application environment
session_write_close();
