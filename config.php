<?php

//--------------------------------------------------------------------------------------- framework
$config["framework"] = array(
	"Aop_Dynamics" => array(
		"List_Controller" => array(
			//array("around", "Default_List_Controller_Configuration", "getListProperties()", "List_Controller_Acls", "getListPropertiesAop")
		) 
	),
	"Dao" => array(
		"class"    => "Mysql_Link",
		"host"     => "localhost",
		"user"     => "SAF-php",
		"password" => "SAF-php",
		"tables"   => array(
			"SAF\\Framework\\Acls_User" => "users",
		)
	),
	"Locale" => array(
		"language" => "fr"
	),
	"View" => array(
		"class" => "Html_View_Engine",
		"css"   => "default"
	)
);

//--------------------------------------------------------------------------------------------- rad
$config["tests"] = array(
	"app" => "Tests",
	"extends" => "framework",
	"Dao" => array(
		"database" => "saf-tests",
		"tables" => array(
			"Test_Order"      => "orders",
			"Test_Order_Line" => "orders_lines",
			"Test_Salesman"   => "salesmen"
		)
	)
);

//--------------------------------------------------------------------------------------------- rad
$config["rad"] = array(
	"app" => "RAD",
	"extends" => "framework",
	"Dao" => array(
		"database" => "saf-rad"
	)
);
