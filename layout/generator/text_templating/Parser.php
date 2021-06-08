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
	public function __construct($object, $root_object = null, string $property_path = '')
	{
		$this->object        = $object;
		$this->root_object   = $root_object;
		$this->property_path = $property_path;
	}

	//----------------------------------------------------------------------------------- elementText
	/**
	 * @param $element          Text
	 * @param $iteration_number integer
	 * @return string
	 */
	public function elementText(Text $element, int $iteration_number = 0) : string
	{
		$text     = $element->text;
		$position = 0;
		while (($position = strpos($text, '{', $position)) !== false) {
			$position            ++;
			$end_position        = strpos($text, '}', $position);
			$property_expression = substr($text, $position, $end_position - $position);
			$value = $this->propertyExpression($property_expression, $iteration_number);
			if (!$value && strpos($property_expression, '?') !== false) {
				return '';
			}
			$text      = substr($text, 0, $position - 1) . $value . substr($text, $end_position + 1);
			$position += strlen($value) - 1;
		}
		return $text;
	}

	//---------------------------------------------------------------------------- propertyExpression
	/**
	 * @param $property_expression string
	 * @param $iteration_number    integer
	 * @return ?string
	 */
	public function propertyExpression(string $property_expression, int $iteration_number = 0)
		: ?string
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
			foreach (explode(DOT, $property_path) as $property_name) {
				if ($property_name[0] === '?') {
					$property_name = str_replace('?', '', $property_name);
				}
				if (
					in_array(substr($property_name, 0, 1), [DQ, Q])
					&& (substr($property_name, -1) === $property_name[0])
				) {
					$object = substr($property_name, 1, -1);
					continue;
				}
				if (!property_exists($object, $property_name)) {
					$property_name = Names::displayToProperty(Loc::rtr(
						Names::propertyToDisplay($property_name),
						get_class($object))
					);
				}
				try {
					$property = new Reflection_Property($object, $property_name);
					$object   = $property->getValue($object);
				}
				catch (ReflectionException) {
					return '';
				}
				if (is_null($object)) {
					break;
				}
				if (is_array($object)) {
					$object = array_slice($object, $iteration_number, 1)[0];
				}
			}
			$value = $object;
			if (isset($property) && $value) {
				$value = Loc::propertyToLocale($property, $value);
			}
			if ($value) {
				break;
			}
		}
		return $value;
	}

}
