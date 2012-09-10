<?php
namespace SAF\Framework;

error_reporting(E_ALL);

require_once "framework/classes/Autoloader.php";
require_once "framework/classes/debug/Execution_Timer.php";
require_once "framework/classes/locale/Html_Translator.php";
require_once "framework/dao/mysql/Mysql_Logger.php";

//Aop::registerBefore("SAF\\Framework\\Aop_Getter->getDatetime()", "SAF\\Framework\\Aop_Tracer::method");

Error_Handlers::getInstance()->addHandler(
	E_ALL & !E_NOTICE,
	new Main_Error_Handler()
)->setAsErrorHandler();

Main_Controller::getInstance()->run($_SERVER["PATH_INFO"], $_GET, $_POST, $_FILES);
