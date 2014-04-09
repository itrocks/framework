<?php
namespace SAF\Framework;

use SAF\AOP\Include_Filter;
use SAF\Framework\Controller\Main;
use SAF\Plugins;

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

touch('update');

// enable running from command line
if (!isset($_SERVER['PATH_INFO'])) $_SERVER['PATH_INFO'] = '/';

// enable AOP cache files : includes must all use this filter
include_once 'framework/core/aop/Include_Filter.php';
Include_Filter::register();
/** @noinspection PhpIncludeInspection */
include_once Include_Filter::file('framework/core/controllers/Main_Controller.php');

// run
echo (new Main())
	->init([
		'framework/components/html_session/Html_Session.php'
	])
	->addTopCorePlugins([
		new Plugins\Manager(),
		new Html_Session([ 'use_cookie' => true ])
	])
	->run($_SERVER['PATH_INFO'], $_GET, $_POST, $_FILES);

// Display result on client browser now, as session serialization could take a moment
ob_flush(); flush();

// When running php on cgi mode, getcwd() will return '/usr/lib/cgi-bin' on specific serialize()
// calls. This is a php bug, calling session_write_close() here will serialize session variables
// within the correct application environment
session_write_close();
