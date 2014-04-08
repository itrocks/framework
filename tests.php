<?php
namespace SAF\Framework;

use SAF\Tests;

global $pwd;
include_once 'pwd.php';
include 'config.php';

$config['tests'] = [
	'app'     => 'Tests',
	'extends' => 'framework',

	//---------------------------------------------------------------------------------------- normal
	'normal' => [
		Dao::class => [
			'database' => 'saf_tests',
			'login'    => 'saf_tests',
			'password' => $pwd['saf_tests'],
			'tables' => [
				Tests\Salesman::class   => 'salesmen'
			]
		],
		Menu::class => [
			['/Application/home', 'Home', '#main'],
			'Friends' => [
				'/Clients'  => 'Clients',
				'/Salesmen' => 'Salesmen'
			],
			'Things' => [
				'/Items'      => 'Items',
				'/Categories' => 'Categories'
			],
			'Documents' => [
				'/Quotes' => 'Quotes',
				'/Orders' => 'Orders'
			],
			'Web' => [
				'/Shops' => 'Shops'
			]
		]
	]

];
