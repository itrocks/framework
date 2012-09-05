<?php
namespace SAF\Framework;

$timestart = microtime(true);

//------------------------------------------------------------------------------- activated modules

require "framework/toolbox/string.php";
require "framework/toolbox/Aop.php";
require "framework/toolbox/Namespaces.php";
require "framework/mappers/Aop_Getter.php";

require "framework/application/Configuration.php";
require "framework/application/Application.php";
require "framework/application/Autoloader.php";
require "framework/application/Html_Session.php";
require "framework/dao/mysql/Mysql_Logger.php";
require "framework/locale/Html_Translator.php";

//------------------------------------------------------------------------------------------- debug

//Aop::registerBefore("SAF\\Framework\\Aop_Getter->getDatetime()", "SAF\\Framework\\Aop_Tracer::method");

//-------------------------------------------------------------------------------------------------

Error_Handlers::getInstance()->addHandler(
	E_ALL & !E_NOTICE,
	new Main_Error_Handler()
)->setAsErrorHandler();

//-------------------------------------------------------------------------------------------------

require "framework/controllers/Main_Controller.php";
Main_Controller::getInstance()->run($_SERVER["PATH_INFO"], $_GET, $_POST, $_FILES);

//-------------------------------------------------------------------------------------------------

echo "<hr />";
echo "durée = " . number_format(microtime(true) - $timestart, 3, ",", " ");
echo "<pre>" . print_r($GLOBALS, true) . "</pre>";
