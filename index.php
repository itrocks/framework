<?php
$timestart = microtime(true);

require "framework/toolbox/Aop.php";
require "framework/dao/mysql/Mysql_Logger.php";
require "framework/application/Session.php";

//-------------------------------------------------------------------------------------------------

require "framework/controllers/Main_Controller.php";
Error_Handlers::getInstance()->addHandler(
	E_ALL & !E_NOTICE,
	new Main_Error_Handler()
)->setAsErrorHandler();

Main_Controller::getInstance()->run($_SERVER["PATH_INFO"], $_GET, $_POST, $_FILES);

//-------------------------------------------------------------------------------------------------

echo "<hr />";
echo "durée = " . number_format(microtime(true) - $timestart, 3, ",", " ");
echo "<pre>" . print_r($GLOBALS, true) . "</pre>";
