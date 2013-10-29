<?php
global $pwd;
include_once "pwd.php";

//------------------------------------------------------------------------------------- my_business
$CONFIG["tests"] = array(
	"app" => "Tests",
	"extends" => "framework",
	'SAF\Framework\Dao' => array(
		"database" => "saf_tests",
		"user"     => "saf_tests",
		"password" => $pwd["saf_tests"],
		"tables" => array(
			'SAF\Framework\Tests\Test_Order'      => "orders",
			'SAF\Framework\Tests\Test_Order_Line' => "orders_lines",
			'SAF\Framework\Tests\Test_Salesman'   => "salesmen"
		)
	),
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
);

require "index.php";
