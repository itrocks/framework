<?php
global $pwd;
include_once "pwd.php";
include "config.php";

//------------------------------------------------------------------------------------- my_business
$config["tests"] = array(
	"app"     => "Tests",
	"extends" => "framework",
	"highest" => array(
		'SAF\Framework\Dao' => array(
			"database" => "saf_tests",
			"user"     => "saf_tests",
			"password" => $pwd["saf_tests"],
			"tables" => array(
				'SAF\Framework\Tests\Test_Order'      => "orders",
				'SAF\Framework\Tests\Test_Order_Line' => "orders_lines",
				'SAF\Framework\Tests\Test_Salesman'   => "salesmen"
			)
		)
	),
	"normal" => array(
		'SAF\Framework\Menu' => array(
			array("/Application/home", "Home", "#main"),
			"Friends" => array(
				"/Clients" => "Clients",
				"/Salesmen"   => "Salesmen"
			),
			"Things" => array(
				"/Items"      => "Items",
				"/Categories" => "Categories"
			),
			"Documents" => array(
				"/Quotes" => "Quotes",
				"/Orders" => "Orders"
			),
			"Web" => array(
				"/Shops" => "Shops"
			)
		)
	)
);
