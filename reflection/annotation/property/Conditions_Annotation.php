<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;

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

	//--------------------------------------------------------------------------------------- applyTo
	/**
	 * Returns true if all conditions apply to a given object
	 *
	 * The object must be of a class compatible with the property class, or it may crash.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @return boolean
	 */
	public function applyTo($object)
	{
		if (!$object) {
			return false;
		}
		foreach ($this->value as $property_name => $condition_value) {
			$condition_values = explode(',', $condition_value);
			/** @noinspection PhpUnhandledExceptionInspection object, property must be valid */
			$property_value   = new Reflection_Property_Value($object, $property_name, $object);
			$value            = $property_value->value();
			if (!in_array($value, $condition_values)) {
				return false;
			}
		}
		return true;
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
