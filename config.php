<?php

//--------------------------------------------------------------------------------------- framework
$config["framework"] = array(
	// core plugins are loaded first, at the beginning of each script
	"core" => array(
		'SAF\Framework\Autoloader',
		'SAF\Framework\Aop_Dealer',
		'SAF\Framework\Builder',
		'SAF\Framework\Error_Handlers' => array(
			array(E_ALL & !E_NOTICE,   'SAF\Framework\Main_Error_Handler'),
			array(E_RECOVERABLE_ERROR, 'SAF\Framework\To_Exception_Error_Handler'),
		)
	),
	// other priorities plugins are loaded when needed, and initialised at session beginning
	// into their priority order
	"highest" => array(
		'SAF\Framework\Aop_Getter',
		'SAF\Framework\Aop_Setter',
		'SAF\Framework\Dao' => array(
			"class"    => 'SAF\Framework\Mysql_Link',
			"host"     => "localhost",
			"user"     => "saf",
			"password" => "saf"
		),
		'SAF\Framework\Locale' => array(
			"date" => "d/m/Y",
			"language" => "fr",
			"number" => array(
				"decimal_minimal_count" => 2,
				"decimal_maximal_count" => 4,
				"decimal_separator"     => ",",
				"thousand_separator"    => " ",
			)
		),
		'SAF\Framework\Mysql_Maintainer',
		'SAF\Framework\View' => array(
			"class" => 'SAF\Framework\Html_View_Engine'
		)
	),
	"higher" => array(),
	"high"   => array(),
	"normal" => array(
		'SAF\Framework\Html_Cleaner',
		'SAF\Framework\Html_Translator',
		'SAF\Framework\Translation_String_Composer',
		'SAF\Framework\Loc'
	),
	"low"    => array(),
	"lower"  => array(),
	"lowest" => array()
);

//--------------------------------------------------------------------------------------------- rad
$config["rad"] = array(
	"app"     => "RAD",
	"extends" => "framework",
	"highest" => array(
		'SAF\Framework\Dao' => array(
			"database" => "saf_rad"
		)
	)
);
