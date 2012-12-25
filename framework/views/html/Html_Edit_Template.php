<?php
namespace SAF\Framework;

class Html_Edit_Template extends Html_Template
{

	//-------------------------------------------------------------------------------------- parseVar
	protected function parseVar($objects, $var_name)
	{
		echo "Html_Edit_Template::parseVar($var_name)<br>";
		return parent::parseVar($objects, $var_name);
	}

}
