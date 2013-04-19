<?php

//--------------------------------------------------------------------------------------- framework
$config["framework"] = array(
	'SAF\Framework\Aop_Dynamics' => array(
		'SAF\Framework\List_Controller' => array(
			//array("around", "SAF\Framework\Default_List_Controller_Configuration", "getListProperties()", "SAF\Framework\List_Controller_Acls", "getListPropertiesAop")
		)
	),
	'SAF\Framework\Dao' => array(
		"class"    => 'SAF\Framework\Mysql_Link',
		"host"     => "localhost",
		"limit"    => 1000,
		"user"     => "saf",
		"password" => "saf",
		"tables"   => array(
			'SAF\Framework\Acls_User' => "users",
		)
	),
	'SAF\Framework\Error_Handlers' => array(
	array(E_ALL & !E_NOTICE,     'SAF\Framework\Main_Error_Handler'),
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
			'SAF\Framework\Html_Session',
			'SAF\Framework\Html_Translator',
			'SAF\Framework\Translation_String_Composer',
			'SAF\Framework\Loc'
		)
	),
	'SAF\Framework\View' => array(
		"class" => 'SAF\Framework\Html_View_Engine',
		"css"   => "default"
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
