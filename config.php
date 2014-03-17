<?php
namespace SAF\Framework;

use SAF\AOP;

//--------------------------------------------------------------------------------------- framework
$config['framework'] = [
	// top core plugins are loaded first, before the session is opened
	// this array must stay empty : top core plugins must be set into the index.php script
	'top_core' => [],
	// core plugins are loaded first, at the beginning of each script, when the session is opened
	'core' => [
		Router::class,
		AOP\Weaver::class,
		Builder::class,
		Xdebug::class
		/*
		Error_Handlers::class => [
			[E_ALL,               Fatal_Error_Handler::class),
			[E_ALL & !E_NOTICE,   Main_Error_Handler::class),
			[E_RECOVERABLE_ERROR, To_Exception_Error_Handler::class),
		)
		*/
	],
	// other priorities plugins are loaded when needed, and initialised at session beginning
	// into their priority order
	'highest' => [
		Dao::class => [
			'class'    => Mysql_Link::class,
			'host'     => 'localhost',
			'login'    => 'saf',
			'password' => 'saf'
		],
		Locale::class => [
			'date'     => 'm/d/Y',
			'language' => 'en',
			'number'   => [
				'decimal_minimal_count' => 2,
				'decimal_maximal_count' => 4,
				'decimal_separator'     => '.',
				'thousand_separator'    => ',',
			]
		],
		View::class => [
			'class' => Html_View_Engine::class,
			'css' => 'default'
		]
	],
	'higher' => [
		Mysql_Maintainer::class
	],
	'high'   => [],
	'normal' => [
		Html_Cleaner::class,
		Html_Translator::class,
		Translation_String_Composer::class,
		Loc::class
	],
	'low'    => [],
	'lower'  => [],
	'lowest' => []
];

//--------------------------------------------------------------------------------------------- rad
$config['rad'] = [
	'app'     => 'RAD',
	'extends' => 'framework',
	'highest' => [
		Dao::class => [
			'database' => 'saf_rad'
		]
	]
];
