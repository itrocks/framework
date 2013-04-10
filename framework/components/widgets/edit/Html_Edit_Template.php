<?php
namespace SAF\Framework;

class Html_Edit_Template extends Html_Template
{

	//-------------------------------------------------------------------------------- parseContainer
	protected function parseContainer($content)
	{
		$i = strpos($content, "<!--BEGIN-->");
		if ($i !== false) {
			$i += 12;
			$j = strrpos($content, "<!--END-->", $i);
			$short_class = Namespaces::shortClassName(get_class($this->object));
			$id = strtolower($short_class) . "_edit";
			$action = "/" . $short_class . "/write";
			$content = substr($content, 0, $i)
				. '<form method="POST" id="' . $id . '" action="' . $action . '">'
				. substr($content, $i, $j - $i)
				. '</form>'
				. substr($content, $j);
		}
		return parent::parseContainer($content);
	}

	//------------------------------------------------------------------------------------ parseValue
	protected function parseValue($objects, $var_name, $as_string = true)
	{
		$property = reset($objects);
		if (($property instanceof Reflection_Property) && ($var_name == "value")) {
			$value = parent::parseValue($objects, $var_name, false);
			$value = (new Html_Builder_Property_Edit($property, $value))->build();
		}
		else {
			$value = parent::parseValue($objects, $var_name, $as_string);
		}
		return $value;
	}

}
