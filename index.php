<?php
namespace ITRocks\Framework;

use ITRocks\Framework\AOP\Include_Filter;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Plugin\Manager;

// php settings
chdir(__DIR__ . '/../..');
error_reporting(E_ALL);
ini_set( 'arg_separator.output',            '&amp;' );
ini_set( 'default_charset',                 'UTF-8' );
ini_set( 'max_execution_time',              30      );
ini_set( 'max_input_vars',                  1000000 );
ini_set( 'memory_limit',                    '1G'    );
ini_set( 'session.use_cookies',             true    );
ini_set( 'xdebug.collect_params',           4       );
ini_set( 'xdebug.max_nesting_level',        255     );
ini_set( 'xdebug.var_display_max_children', 10      );
ini_set( 'xdebug.var_display_max_data',     150     );
ini_set( 'xdebug.var_display_max_depth',    3       );
putenv('LANG=fr_FR.UTF-8');
set_time_limit(30);

// enable running from command line
if (!isset($_SERVER['PATH_INFO'])) {
	$_SERVER['PATH_INFO'] = '/';
}

// wait for unlock
while (is_file('lock')) {
	usleep(100000);
	clearstatcache(true, 'lock');
}

// enable cache files for compiled scripts : includes must all use this filter
include_once __DIR__ . '/aop/Include_Filter.php';
Include_Filter::register(getcwd());
// enable autoloader
/** @noinspection PhpIncludeInspection */
include_once Include_Filter::file(__DIR__ . '/Autoloader.php');
(new Autoloader)->register(getcwd());

// run main controller
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
