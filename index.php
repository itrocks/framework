<?php
namespace SAF\Framework;

use SAF\Framework\AOP\Include_Filter;
use SAF\Framework\Controller\Main;
use SAF\Framework\Plugin\Manager;

// php settings
error_reporting(E_ALL);
ini_set('arg_separator.output', '&amp;');
ini_set('default_charset', 'UTF-8');
ini_set('max_input_vars', 1000000);
ini_set('memory_limit', '1024M');
ini_set('session.use_cookies', true);
ini_set('xdebug.collect_params', 4);
ini_set('xdebug.max_nesting_level', 255);
//ini_set('xdebug.scream', true);
ini_set('xdebug.var_display_max_children', 10);
ini_set('xdebug.var_display_max_data', 50);
ini_set('xdebug.var_display_max_depth', 3);
set_time_limit(30);
//&XDEBUG_PROFILE=1

// TODO DEBUG remove this
touch('update');

// enable running from command line
if (!isset($_SERVER['PATH_INFO'])) $_SERVER['PATH_INFO'] = '/';

// enable cache files for compiled scripts : includes must all use this filter
include_once 'saf/framework/aop/Include_Filter.php';
Include_Filter::register();
// enable autoloader
/** @noinspection PhpIncludeInspection */
include_once Include_Filter::file('saf/framework/Autoloader.php');
(new Autoloader)->register();

// run main controller
echo (new Main)
	->init()
	->addTopCorePlugins([
		new Manager,
		new Html_Session(['use_cookie' => true])
	])
	->run($_SERVER['PATH_INFO'], $_GET, $_POST, $_FILES);

// Display result on client browser now, as session serialization could take a moment
ob_flush(); flush();

// When running php on cgi mode, getcwd() will return '/usr/lib/cgi-bin' on specific serialize()
// calls. This is a php bug, calling session_write_close() here will serialize session variables
// within the correct application environment
session_write_close();

echo 'Ok.';
