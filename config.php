<?php

//--------------------------------------------------------------------------------------- framework
$config["framework"] = array(
	"Aop_Dynamics" => array(
		"List_Controller" => array(
			//array("around", "Default_List_Controller_Configuration", "getListProperties()", "List_Controller_Acls", "getListPropertiesAop")
		)
	),
	"Dao" => array(
		"class"    => "Mysql_Link",
		"host"     => "localhost",
		"limit"    => 1000,
		"user"     => "saf",
		"password" => "saf",
		"tables"   => array(
			"Acls_User" => "users",
		)
	),
	"Error_Handlers" => array(
		array(E_ALL & !E_NOTICE,   "Main_Error_Handler"),
		array(E_RECOVERABLE_ERROR, "To_Exception_Error_Handler"),
	),
	"Locale" => array(
		"date" => "d/m/Y",
		"language" => "fr",
		"number" => array(
				"decimal_minimal_count" => 2,
				"decimal_maximal_count" => 4,
				"decimal_separator"     => ",",
				"thousand_separator"    => " ",
		)
	),
	"Plugins" => array(
		"highest" => array(
			"Mysql_Maintainer",
			"Aop_Getter",
			"Aop_Setter",
		),
		"normal" => array(
			"Html_Cleaner",
			"Html_Session",
			"Html_Translator",
			"Translation_String_Composer",
			"Loc"
		)
	),
	"View" => array(
		"class" => "Html_View_Engine",
		"css"   => "default"
	)
);

//--------------------------------------------------------------------------------------------- rad
$config["rad"] = array(
	"app" => "RAD",
	"extends" => "framework",
	"Dao" => array(
		"database" => "saf-rad"
	)
);

// top-top level plugins (temporary place)
require_once "framework/components/html_session/Html_Session.php";
SAF\Framework\Html_Session::register();
