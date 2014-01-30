<?php
namespace SAF\Framework;

// php settings
error_reporting(E_ALL);
ini_set("arg_separator.output", "&amp;");
ini_set("default_charset", "UTF-8");
ini_set("max_input_vars", 1000000);
ini_set("memory_limit", "1024M");
ini_set("session.use_cookies", true);
ini_set("xdebug.collect_params", 3);
ini_set("xdebug.max_nesting_level", 255);
//ini_set("xdebug.scream", true);
ini_set("xdebug.var_display_max_children", 1000000);
ini_set("xdebug.var_display_max_data", 1000000);
ini_set("xdebug.var_display_max_depth", 1000000);
set_time_limit(20);
//&XDEBUG_PROFILE=1

// run
$_PATH_INFO = isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "/";
require_once "framework/core/controllers/Main_Controller.php";
echo Main_Controller::getInstance()->run($_PATH_INFO, $_GET, $_POST, $_FILES);

// Display result on client browser now, as session serialization could take a moment
ob_flush(); flush();

// When running php on cgi mode, getcwd() will return "/usr/lib/cgi-bin" on specific serialize()
// calls. This is a php bug, calling session_write_close() here will serialize session variables
// within the correct application environment
session_write_close();
