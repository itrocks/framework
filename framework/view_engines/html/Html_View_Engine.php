<?php
namespace Framework;

class Html_View_Engine implements View_Engine
{

	//-------------------------------------------------------------------------- getPossibleTemplates
	public static function getPossibleTemplates($class_name, $feature_name)
	{
		return array(
			$class_name . "_" . $feature_name . ".html",
			"Html_$feature_name.html"
		);
	}

}
