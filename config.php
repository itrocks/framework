<?php

//--------------------------------------------------------------------------------------- framework
$config["framework"] = array(
	'SAF\Framework\Dao' => array(
		"class"    => 'SAF\Framework\Mysql_Link',
		"host"     => "localhost",
		"user"     => "saf",
		"password" => "saf"
	),
	'SAF\Framework\Error_Handlers' => array(
		array(E_ALL & !E_NOTICE,   'SAF\Framework\Main_Error_Handler'),
		array(E_RECOVERABLE_ERROR, 'SAF\Framework\To_Exception_Error_Handler'),
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
	'SAF\Framework\Plugins' => array(
		"top" => array(
			'SAF\Framework\Builder'
		),
		"highest" => array(
			'SAF\Framework\Mysql_Maintainer',
			'SAF\Framework\Aop_Getter',
			'SAF\Framework\Aop_Setter',
		),
		"normal" => array(
			'SAF\Framework\Html_Cleaner',
			'SAF\Framework\Html_Translator',
			'SAF\Framework\Translation_String_Composer',
			'SAF\Framework\Loc'
		)
	),
	'SAF\Framework\View' => array(
		"class" => 'SAF\Framework\Html_View_Engine'
	)
);

//--------------------------------------------------------------------------------------------- rad
$config["rad"] = array(
	"app" => "RAD",
	"extends" => "framework",
	'SAF\Framework\Dao' => array(
		"database" => "saf_rad"
	)
);
