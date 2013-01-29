<?php
namespace SAF\Framework;

// php settings
set_time_limit(5);
ini_set("max_input_vars", 1000000);
ini_set("memory_limit", "1024M");
//ini_set("xdebug.scream", true);
ini_set("xdebug.collect_params", 4);
ini_set("xdebug.var_display_max_children", 1000000);
ini_set("xdebug.var_display_max_data", 1000000);
ini_set("xdebug.var_display_max_depth", 1000000);
ini_set("default_charset", "UTF-8");

// init
error_reporting(E_ALL);
require_once "framework/core/Autoloader.php";
Autoloader::register();

// TODO 'better use something like a Modules::register() call
if (!isset($MODULES)) $MODULES = array();
foreach (array_reverse($MODULES) as $MODULE) $MODULE();

// run
$_PATH_INFO = isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "/";
Main_Controller::getInstance()->run($_PATH_INFO, $_GET, $_POST, $_FILES);

//echo "<pre>" . print_r($GLOBALS, true) . "</pre>";
//echo "<pre>\$_POST=unserialize(\"" . str_replace("\"", "\\\"", serialize($_POST)) . "\")</pre>";
