<?php
namespace SAF\Framework;

use SAF\Tests;

global $pwd;
include_once 'pwd.php';
include 'config.php';

$config['tests'] = array(
	'app'     => 'Tests',
	'extends' => 'framework',

	//--------------------------------------------------------------------------------------- highest
	'highest' => array(
		Dao::class => array(
			'database' => 'saf_tests',
			'login'    => 'saf_tests',
			'password' => $pwd['saf_tests'],
			'tables' => array(
				Tests\Salesman::class   => 'salesmen'
			)
		)
	),

	//---------------------------------------------------------------------------------------- normal
	'normal' => array(
		Menu::class => array(
			array('/Application/home', 'Home', '#main'),
			'Friends' => array(
				'/Clients'  => 'Clients',
				'/Salesmen' => 'Salesmen'
			),
			'Things' => array(
				'/Items'      => 'Items',
				'/Categories' => 'Categories'
			),
			'Documents' => array(
				'/Quotes' => 'Quotes',
				'/Orders' => 'Orders'
			),
			'Web' => array(
				'/Shops' => 'Shops'
			)
		)
	)

);
