<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Conditions annotation
 *
 * Conditions annotation declares other property names that are used to know if the property can
 * have a value
 *
 * Use : @conditions property_name, another_property
 */
class Conditions_Annotation extends List_Annotation implements Property_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'conditions';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $property)
	{
		parent::__construct($value);

		// initialize value on an associative way
		if ($this->value) {
			$conditions = [];
			foreach ($this->value as $condition) {
				if (strpos($condition, '=')) {
					list($property_name, $condition) = explode('=', $condition);
				}
				else {
					$property_name = $condition;
				}
				if (
					in_array($condition, [_FALSE, _TRUE])
					&& $property->getFinalClass()->getProperty($property_name)->getType()->isBoolean()
				) {
					$condition = ($condition === _TRUE) ? 1 : 0;
				}
				$conditions[$property_name] = isset($conditions[$property_name])
					? ($conditions[$property_name] . ',' . $condition)
					: $condition;
			}
			$this->value = $conditions;
		}
	}

	//-------------------------------------------------------------------------- asHtmlAttributeValue
	/**
	 * @return string
	 * @todo NORMAL should take care of a given property path prefix (as argument)
	 */
	public function asHtmlAttributeValue()
	{
		$html_conditions = [];
		foreach ($this->value as $condition_name => $condition_value) {
			$html_conditions[] = $condition_name . '=' . $condition_value;
		}
		return join(';', $html_conditions);
	}

}
