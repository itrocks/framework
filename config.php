<?php

//--------------------------------------------------------------------------------------- framework
$config["framework"] = array(
	"Dao" => array(
		"class"     => "Mysql_Link",
		"host"      => "localhost",
		"user"      => "SAF-php",
		"password"  => "SAF-php"
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
$config["rad"] = array(
	"app" => "RAD",
	"extends" => "framework",
	"Dao" => array(
		"database" => "saf_rad"
	)
);

//--------------------------------------------------------------------------------------------- rad
$config["tests"] = array(
		"app" => "Tests",
		"extends" => "framework",
		"Dao" => array(
			"database" => "saf_test",
			"tables" => array(
				"Test_Order"      => "orders",
				"Test_Order_Line" => "orders_lines",
				"Test_Salesman"   => "salesmen"
			)
		)
);
