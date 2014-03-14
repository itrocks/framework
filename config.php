<?php
namespace SAF\Framework;

use SAF\AOP;

//--------------------------------------------------------------------------------------- framework
$config['framework'] = array(
	// top core plugins are loaded first, before the session is opened
	// this array must stay empty : top core plugins must be set into the index.php script
	'top_core' => array(
	),
	// core plugins are loaded first, at the beginning of each script, when the session is opened
	'core' => array(
		Router::class,
		AOP\Weaver::class,
		Builder::class,
		Xdebug::class
		/*
		Error_Handlers::class => array(
			array(E_ALL,               Fatal_Error_Handler::class),
			array(E_ALL & !E_NOTICE,   Main_Error_Handler::class),
			array(E_RECOVERABLE_ERROR, To_Exception_Error_Handler::class),
		)
		*/
	),
	// other priorities plugins are loaded when needed, and initialised at session beginning
	// into their priority order
	'highest' => array(
		Dao::class => array(
			'class'    => Mysql_Link::class,
			'host'     => 'localhost',
			'login'    => 'saf',
			'password' => 'saf'
		),
		Locale::class => array(
			'date' => 'm/d/Y',
			'language' => 'en',
			'number' => array(
				'decimal_minimal_count' => 2,
				'decimal_maximal_count' => 4,
				'decimal_separator'     => '.',
				'thousand_separator'    => ',',
			)
		),
		View::class => array(
			'class' => Html_View_Engine::class,
			'css' => 'default'
		)
	),
	'higher' => array(
		Mysql_Maintainer::class
	),
	'high'   => array(),
	'normal' => array(
		Html_Cleaner::class,
		Html_Translator::class,
		Translation_String_Composer::class,
		Loc::class
	),
	'low'    => array(),
	'lower'  => array(),
	'lowest' => array()
);

//--------------------------------------------------------------------------------------------- rad
$config['rad'] = array(
	'app'     => 'RAD',
	'extends' => 'framework',
	'highest' => array(
		Dao::class => array(
			'database' => 'saf_rad'
		)
	)
);
