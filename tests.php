<?php
global $pwd;
include_once "pwd.php";

//------------------------------------------------------------------------------------- my_business
$CONFIG["tests"] = array(
	"app" => "Tests",
	"extends" => "framework",
	'SAF\Framework\Builder' => array(
		'SAF\Framework\User' => 'SAF\Framework\Acls_User'
	),
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
			"color" => "green",
			"/Clients" => "Clients",
			"/Salesmen"   => "Salesmen"
		),
		"Things" => array(
			"color" => "blue",
			"/Items"      => "Items",
			"/Categories" => "Categories"
		)
	),
	'SAF\Framework\Plugins' => array(
		"normal" => array(
			'SAF\Framework\Acls',
			'SAF\Framework\Acls_List_Properties',
			'SAF\Framework\Acls_Output_Properties'
		)
	),
	'SAF\Framework\View' => array(
		"css" => "next"
	)
);

require "index.php";
