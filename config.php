<?php
namespace ITRocks\Framework;

use ITRocks\Framework\AOP\Weaver;
use ITRocks\Framework\Assets\Template_Compiler;
use ITRocks\Framework\Dao\Mysql;
use ITRocks\Framework\Dao\Mysql\Link;
use ITRocks\Framework\Debug\Xdebug;
use ITRocks\Framework\Locale\Html_Translator;
use ITRocks\Framework\Locale\Language;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Locale\Number_Format;
use ITRocks\Framework\PHP\Compiler;
use ITRocks\Framework\Plugin\Priority;
use ITRocks\Framework\Tests\Tests_Configurator;
use ITRocks\Framework\Tests\Tests_Html_ResultPrinter;
use ITRocks\Framework\Tools\Feature_Class\Menu_Update;
use ITRocks\Framework\Updater\Application_Updater;
use ITRocks\Framework\View\Html\Cleaner;

global $loc, $pwd;
require __DIR__ . '/../../loc.php';
require __DIR__ . '/../../pwd.php';

$config['ITRocks/Framework'] = [
	Configuration::APP         => Application::class,
	Configuration::AUTHOR      => 'Baptiste Pillot',
	Configuration::ENVIRONMENT => $loc[Configuration::ENVIRONMENT],

	//---------------------------------------------------------------------------- Priority::TOP_CORE
	// top core plugins are loaded first, before the session is opened
	// this array must stay empty : top core plugins must be set into the index.php script
	Priority::TOP_CORE => [],

	//-------------------------------------------------------------------------------- Priority::CORE
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

	//-------------------------------------------------------------------------------- Priority::LOW-
	Priority::LOWEST => [],
	Priority::LOWER  => [],
	Priority::LOW    => [
		// lower to execute this before Mysql\Maintainer
		Mysql\Reconnect::class
	],

	//------------------------------------------------------------------------------ Priority::NORMAL
	Priority::NORMAL => [
		Cleaner::class,
		Compiler::class => [
			1 => [Router::class, Builder\Compiler::class],
			2 => [Builder\Linked_Classes_Compiler::class],
			3 => [AOP\Compiler::class],
			4 => [Mysql\Compiler::class]
		],
		Dao::class => [
			Configuration::CLASS_NAME => Link::class,
			Link::DATABASE => $loc[Link::class][Link::DATABASE],
			Link::HOST     => $loc[Link::class][Link::HOST] ?? '127.0.0.1',
			Link::LOGIN    => $loc[Link::class][Link::LOGIN],
			Link::PASSWORD => $pwd[Link::class],
			Link::PORT     => $loc[Link::class][Link::PORT]   ?? 3306,
			Link::SOCKET   => $loc[Link::class][Link::SOCKET] ?? null
		],
		Html_Translator::class,
		Loc::class,
		Locale::class => [
			Locale::DATE     => 'm/d/Y',
			Locale::LANGUAGE => Language::EN,
			Locale::NUMBER   => [
				Number_Format::DECIMAL_MINIMAL_COUNT => 2,
				Number_Format::DECIMAL_MAXIMAL_COUNT => 4,
				Number_Format::DECIMAL_SEPARATOR     => DOT,
				Number_Format::THOUSAND_SEPARATOR    => ','
			]
		],
		Menu_Update::class,
		Mysql\Maintainer::class,
		Template_Compiler::class,
		Tests_Configurator::class => [
			Tests_Configurator::PHPUNIT_OPTIONS => [
				__DIR__ . '/../../vendor/bin/phpunit',
				'configuration' => __DIR__ . '/../../phpunit.xml.dist',
				'printer'       => Tests_Html_ResultPrinter::class
			]
		],
		View::class => [
			Configuration::CLASS_NAME => View\Html\Engine::class,
			View\Html\Engine::CSS     => View\Html\Engine::CSS_DEFAULT
		],
	],

	//------------------------------------------------------------------------------- Priority::HIGH+
	Priority::HIGH    => [],
	Priority::HIGHER  => [],
	Priority::HIGHEST => [],

	//------------------------------------------------------------------------------ Priority::REMOVE
	Priority::REMOVE  => []

];
