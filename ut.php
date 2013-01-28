<?php

//------------------------------------------------------------------------------------- my_business
$CONFIG["tests"] = array(
	"app" => "Tests",
	"extends" => "framework",
	"Dao" => array(
		"database" => "saf_tests",
		"tables" => array(
			"Test_Order"      => "orders",
			"Test_Order_Line" => "orders_lines",
			"Test_Salesman"   => "salesmen"
		)
	)
);

require "index.php";
