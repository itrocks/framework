<?php
namespace SAF\Tests;

use SAF\Framework\Dao;
use SAF\Framework\Widget\Menu;
use SAF\Tests\Objects\Salesman;

global $pwd;
include_once 'pwd.php';
include 'config.php';

$config['tests'] = [
	'app'     => Application::class,
	'extends' => 'framework',

	//---------------------------------------------------------------------------------------- normal
	'normal' => [
		Dao::class => [
			'database' => 'saf_tests',
			'login'    => 'saf_tests',
			'password' => $pwd['saf_tests'],
			'tables' => [
				Salesman::class   => 'salesmen'
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
