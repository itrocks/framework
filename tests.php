<?php

//------------------------------------------------------------------------------------- my_business
$CONFIG["tests"] = array(
	"app" => "Tests",
	"extends" => "framework",
	'SAF\Framework\Dao' => array(
		"database" => "saf_tests",
		"tables" => array(
			'SAF\Framework\Tests\Test_Order'      => "orders",
			'SAF\Framework\Tests\Test_Order_Line' => "orders_lines",
			'SAF\Framework\Tests\Test_Salesman'   => "salesmen"
		)
	)
);

require "index.php";
