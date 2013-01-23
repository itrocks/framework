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
		"limit"    => 1000,
		"user"     => "SAF-php",
		"password" => "SAF-php",
		"tables"   => array(
			"Acls_User" => "users",
		)
	),
	"Locale" => array(
		"date" => "d/m/Y",
		"language" => "fr",
		"number" => array(
				"decimal_minimal_count" => 2,
				"decimal_maximal_count" => 4,
				"decimal_separator"     => ",",
				"thousand_separator"    => " ",
		)
	),
	"View" => array(
		"class" => "Html_View_Engine",
		"css"   => "default"
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
