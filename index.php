<?php
namespace SAF\Framework;

// init
error_reporting(E_ALL);
require_once "framework/classes/Autoloader.php";
Autoloader::register();

if (!isset($_SERVER["SAF_PATH"])) $_SERVER["SAF_PATH"] = __DIR__;
if (!isset($_SERVER["SAF_ROOT"])) $_SERVER["SAF_ROOT"] = substr(__DIR__, strlen($_SERVER["DOCUMENT_ROOT"]));

// debug priority modules
//ini_set("xdebug.scream", true);
ini_set("xdebug.collect_params", 4);
//Aop_Logger::register();
//Execution_Timer::register();
Mysql_Logger::register();
Xdebug::register();
//Class_Debugger::register("Html_Template");
//aop_add_before(__NAMESPACE__ . "\\Aop_Getter->getDatetime()", __NAMESPACE__ . "\\Aop_Tracer::method");

// high priority main error handlers
Error_Handlers::register(E_ALL & !E_NOTICE, new Main_Error_Handler());
Error_Handlers::register(E_RECOVERABLE_ERROR, new To_Exception_Error_Handler());
// high priority modules (Aop_Getter and Aop_Setter will disapear with 5.5, hopefully)
Aop_Getter::register();
Aop_Setter::register();
// normal priority modules
Acls_Loader::register();
Aop_Dynamics::register();
Html_Cleaner::register();
Html_Session::register();
Html_Translator::register();
List_Controller_Acls::register();
Mysql_Maintainer::register();
Object_Builder::register();
// activate errors handlers
Error_Handlers::activate();

// TODO 'better use something like a Modules::register() call 
if (!isset($MODULES)) $MODULES = array();
foreach (array_reverse($MODULES) as $MODULE) $MODULE();

// run
$_PATH_INFO = isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "/";
Main_Controller::getInstance()->run($_PATH_INFO, $_GET, $_POST, $_FILES);

echo "<pre>" . print_r($GLOBALS, true) . "</pre>";
