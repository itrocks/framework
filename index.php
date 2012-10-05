<?php
namespace SAF\Framework;
use SAF\Framework\Tests\Menu_Tester;

error_reporting(E_ALL);
require_once "framework/classes/Autoloader.php";
Autoloader::register();

// debug
ini_set("xdebug.scream", false);
//Aop_Logger::register();
//Execution_Timer::register();
//Mysql_Logger::register();
Xdebug::register();
//aop_add_before(__NAMESPACE__ . "\\Aop_Getter->getDatetime()", __NAMESPACE__ . "\\Aop_Tracer::method");

// modules
Aop_Getter::register();
Aop_Setter::register();
Html_Translator::register();
Html_Cleaner::register();

// tests
Menu_Tester::register();

Error_Handlers::getInstance()->addHandler(
	E_ALL & !E_NOTICE,
	new Main_Error_Handler()
)->setAsErrorHandler();

$_PATH_INFO = isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "/";

Main_Controller::getInstance()->run($_PATH_INFO, $_GET, $_POST, $_FILES);
