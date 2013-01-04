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
			$content = substr($content, 0, $i)
				. '<form method="POST">'
				. substr($content, $i, $j - $i)
				. '</form>'
				. substr($content, $j);
		}
		return parent::parseContainer($content);
	}

	//----------------------------------------------------------------------------- parseDisplayAfter
	protected function parseDisplayAfter(&$content, $objects, $i)
	{
		$property = reset($objects);
		$type = $property->getType();
		if ($class_name = Type::isMultiple($type)) {
			$span = new Html_Span("+");
			$span->addClass("plus");
			$content = substr($content, 0, $i) . $span . substr($content, $i);
		}
	}

	//------------------------------------------------------------------------------------ parseValue
	protected function parseValue($objects, $var_name)
	{
		$property = reset($objects);
		if (($var_name == "value") && ($property instanceof Reflection_Property)) {
			$value = parent::parseValue($objects, $var_name, false);
			$value = (new Html_Builder_Property_Edit($property, $value))->build();
		}
		else {
			$value = parent::parseValue($objects, $var_name);
		}
		return $value;
	}

	//-------------------------------------------------------------------------------------- parseVar
	protected function parseVar(&$content, $objects, $i, $j)
	{
		$var_name = substr($content, $i, $j - $i);
		if (($var_name == "display") && (reset($objects) instanceof Reflection_Property)) {
			$k = $j + 1;
			if ($content[$k] == "|") {
				$k ++;
			}
			$this->parseDisplayAfter($content, $objects, $k);
		}
		return parent::parseVar($content, $objects, $i, $j);
	}

}
