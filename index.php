<?php
namespace SAF\Framework;
use SAF\Framework\Tests\Menu_Tester;

error_reporting(E_ALL);
require_once "framework/classes/Autoloader.php";
Autoloader::register();

// debug
//ini_set("xdebug.scream", true);
//Aop_Logger::register();
Execution_Timer::register();
//Mysql_Logger::register();
Xdebug::register();
//Class_Debugger::register("Html_Template");
//aop_add_before(__NAMESPACE__ . "\\Aop_Getter->getDatetime()", __NAMESPACE__ . "\\Aop_Tracer::method");

// modules
Error_Handlers::register(E_ALL & !E_NOTICE, new Main_Error_Handler());
Error_Handlers::register(E_RECOVERABLE_ERROR, new To_Exception_Error_Handler());
Error_Handlers::activate();
Aop_Getter::register();
Aop_Setter::register();
Html_Cleaner::register();
Html_Translator::register();

// tests
Menu_Tester::register();

$_PATH_INFO = isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "/";

Main_Controller::getInstance()->run($_PATH_INFO, $_GET, $_POST, $_FILES);
