<?php
namespace SAF\Framework;

use SAF\Framework\AOP;
use SAF\Framework\AOP\Weaver;
use SAF\Framework\Builder;
use SAF\Framework\Dao\Mysql;
use SAF\Framework\Dao\Mysql\Link;
use SAF\Framework\Debug\Xdebug;
use SAF\Framework\Locale;
use SAF\Framework\Locale\Html_Translator;
use SAF\Framework\Locale\Language;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Locale\Number_Format;
use SAF\Framework\Locale\Translation_String_Composer;
use SAF\Framework\PHP\Compiler;
use SAF\Framework\Plugin\Priority;
use SAF\Framework\Updater\Application_Updater;
use SAF\Framework\View\Html\Cleaner;

global $loc, $pwd;
require __DIR__ . '/../../loc.php';
require __DIR__ . '/../../pwd.php';

$config['SAF/Framework'] = [
	Configuration::APP         => Application::class,
	Configuration::AUTHOR      => 'Baptiste Pillot',
	Configuration::ENVIRONMENT => $loc[Configuration::ENVIRONMENT],

	// top core plugins are loaded first, before the session is opened
	// this array must stay empty : top core plugins must be set into the index.php script
	Priority::TOP_CORE => [],

	// core plugins are registered first on session creation
	// they are activated first, at the beginning of each script
	// here must be only plugins that are needed in 100% scripts, as a lot of them may consume time
	Priority::CORE => [
		Router::class,              // ! must be the first core plugins as others plugins need it
		Weaver::class,              // ! must be declared before any plugin that uses AOP
		Builder::class,             // ! every classes before Builder will not be replaceable
		Application_Updater::class, // check for update at each script call
		Xdebug::class               // remove xdebug parameters at each script call
		/*
		Error_Handlers::class => [
			[E_ALL,               Fatal_Error_Handler::class),
			[E_ALL & !E_NOTICE,   Main_Error_Handler::class),
			[E_RECOVERABLE_ERROR, To_Exception_Error_Handler::class),
		)
		*/
	],

	// other priorities plugins are activated only when needed
	// the priority says if what programming level their AOP orders will be executed :
	// ie if two plugins have the same pointcut, the highest priority advice will be executed,
	// and the lowest priority advice will be executed only if the highest processes wants it.

	Priority::LOWEST => [],
	Priority::LOWER  => [],
	Priority::LOW    => [],

	Priority::NORMAL  => [
		Cleaner::class,
		Compiler::class => [
			1 => [
				Router::class,
				Builder\Compiler::class
			],
			2 => [
				Builder\Linked_Classes_Compiler::class,
				AOP\Compiler::class,
			],
			3 => [
				Mysql\Compiler::class
			]
		],
		Dao::class => [
			Configuration::CLASS_NAME => Link::class,
			Link::DATABASE => 'saf_demo',
			Link::HOST     => 'localhost',
			Link::LOGIN    => 'saf_demo',
			Link::PASSWORD => $pwd['saf_demo'],
		],
		Html_Translator::class,
		Loc::class,
		Locale::class => [
			Locale::DATE     => 'm/d/Y',
			Locale::LANGUAGE => Language::EN,
			Locale::NUMBER   => [
				Number_Format::DECIMAL_MINIMAL_COUNT => 2,
				Number_Format::DECIMAL_MAXIMAL_COUNT => 4,
				Number_Format::DECIMAL_SEPARATOR     => '.',
				Number_Format::THOUSAND_SEPARATOR    => ',',
			]
		],
		Mysql\Maintainer::class,
		Translation_String_Composer::class,
		View::class => [
			Configuration::CLASS_NAME => View\Html\Engine::class,
			View\Html\Engine::CSS => View\Html\Engine::CSS_DEFAULT
		]
	],

	Priority::HIGH    => [],
	Priority::HIGHER  => [],
	Priority::HIGHEST => [],
	Priority::REMOVE  => []

];
