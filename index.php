<?php
namespace SAF\Framework;

error_reporting(E_ALL);
require_once "framework/classes/Autoloader.php";

// modules
require_once "framework/classes/locale/Html_Translator.php";

// debug
ini_set("xdebug.scream", false);
//require_once "framework/classes/debug/Execution_Timer.php";
//require_once "framework/classes/debug/Xdebug.php";
//require_once "framework/classes/loggers/Aop_Logger.php";
//require_once "framework/dao/mysql/Mysql_Logger.php";

//Aop::registerBefore(__NAMESPACE__ . "\\Aop_Getter->getDatetime()", __NAMESPACE__ . "\\Aop_Tracer::method");

// tests
require_once "framework/tests/test_objects/Menu_Tester.php";

Error_Handlers::getInstance()->addHandler(
	E_ALL & !E_NOTICE,
	new Main_Error_Handler()
)->setAsErrorHandler();

$_PATH_INFO = isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "/"; 

Main_Controller::getInstance()->run($_PATH_INFO, $_GET, $_POST, $_FILES);
