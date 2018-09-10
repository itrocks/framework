<?php
namespace ITRocks\Framework\Layout\Generator\Text_Templating;

use ITRocks\Framework\Layout\Generator\Text_Templating;
use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Property\Reflection_Property;
use ITRocks\Framework\Tools\Names;
use ReflectionException;

/**
 * Text element parser
 */
class Parser
{

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	protected $object;

	//-------------------------------------------------------------------------------- $property_path
	/**
	 * @var string
	 */
	protected $property_path;

	//---------------------------------------------------------------------------------- $root_object
	/**
	 * @var object
	 */
	protected $root_object;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $object        object
	 * @param $root_object   object
	 * @param $property_path string reference property path
	 */
	public function __construct($object, $root_object = null, $property_path = '')
	{
		$this->object        = $object;
		$this->root_object   = $root_object;
		$this->property_path = $property_path;
	}

	//----------------------------------------------------------------------------------- elementText
	/**
	 * @param $element Text
	 * @return string
	 */
	public function elementText(Text $element)
	{
		$text     = $element->text;
		$position = 0;
		while (($position = strpos($text, '{', $position)) !== false) {
			$position            ++;
			$end_position        = strpos($text, '}', $position);
			$property_expression = substr($text, $position, $end_position - $position);
			$value     = $this->propertyExpression($property_expression);
			$text      = substr($text, 0, $position - 1) . $value . substr($text, $end_position + 1);
			$position += strlen($value) - 1;
		}
		return $text;
	}

	//---------------------------------------------------------------------------- propertyExpression
	/**
	 * @param $property_expression string
	 * @return string
	 */
	public function propertyExpression($property_expression)
	{
		$value = '';
		foreach (explode('?:', $property_expression) as $property_path) {
			if (in_array($property_path, Text_Templating::PAGE_PROPERTY_PATHS)) {
				return '{' . $property_path . '}';
			}
			$object = $this->object;
			if ($this->property_path) {
				if (beginsWith($property_path, $this->property_path . DOT)) {
					$property_path = substr($property_path, strlen($this->property_path) + 1);
				}
				else {
					$object = $this->root_object;
				}
			}
			try {
				$property = new Reflection_Property(get_class($object), $property_path);
				$value    = Loc::propertyToLocale($property, $property->getValue($object));
			}
			catch (ReflectionException $exception) {
				$value = '{' . Loc::tr($exception) . '}';
			}
			if ($value) {
				return $value;
			}
		}
		return $value;
	}

}
