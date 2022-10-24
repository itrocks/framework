<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Reflection\Type;

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

	//----------------------------------------------------------------------------------------- EMPTY
	const EMPTY = '@empty';

	//------------------------------------------------------------------------------------------- SET
	const SET = '@set';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    ?string
	 * @param $property Reflection_Property
	 */
	public function __construct(?string $value, Reflection_Property $property)
	{
		parent::__construct($value);

		// initialize value on an associative way
		if ($this->value) {
			$conditions = [];
			foreach ($this->value as $condition) {
				if ($position = strpos($condition, '>')) {
					$operator = (($condition[$position + 1] === '=') ? '>=' : '>');
				}
				elseif ($position = strpos($condition, '<')) {
					$operator = (($condition[$position + 1] === '=') ? '<=' : '<');
				}
				elseif (str_contains($condition, '=')) {
					$operator = '=';
				}
				else {
					$operator = '';
				}
				if ($operator) {
					[$property_name, $condition] = explode($operator, $condition);
					$condition = (($operator === '=') ? '' : $operator) . $condition;
				}
				else {
					$property_name = $condition;
					if (str_starts_with($property_name, '!')) {
						$condition     = static::EMPTY;
						$property_name = substr($property_name, 1);
					}
					elseif ($this->typeOf($property, $property_name)->isClass()) {
						$condition = static::SET;
					}
				}
				if (
					in_array($condition, [_FALSE, _TRUE])
					&& $this->typeOf($property, $property_name)->isBoolean()
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
	 * @param $object ?object
	 * @return boolean
	 */
	public function applyTo(?object $object) : bool
	{
		if (!$object) {
			return false;
		}
		foreach ($this->value as $property_name => $condition_value) {
			$condition_values = explode(',', $condition_value);
			/** @noinspection PhpUnhandledExceptionInspection object, property must be valid */
			$property_value = new Reflection_Property_Value($object, $property_name, $object);
			$value          = $property_value->value();
			foreach ($condition_values as $condition) {
				$op = (substr($condition, 1, 1) === '=')
					? substr($condition, 0, 2)
					: substr($condition, 0, 1);
				if (!(
					(isset($value) && ($condition === static::SET))
					|| (!$value    && ($condition === static::EMPTY))
					|| (($op === '>')  && ($value >  substr($condition, 1)))
					|| (($op === '>=') && ($value >= substr($condition, 2)))
					|| (($op === '<')  && ($value <  substr($condition, 1)))
					|| (($op === '<=') && ($value <= substr($condition, 2)))
					|| (($value === $condition) && !in_array($op, ['>', '<']))
				)) {
					return false;
				}
			}
		}
		return true;
	}

	//-------------------------------------------------------------------------- asHtmlAttributeValue
	/**
	 * @return string
	 * @todo NORMAL should take care of a given property path prefix (as argument)
	 */
	public function asHtmlAttributeValue() : string
	{
		$html_conditions = [];
		foreach ($this->value as $condition_name => $condition_value) {
			$operator          = (in_array(substr($condition_value, 0, 1), ['<', '>']) ? '' : '=');
			$html_conditions[] = $condition_name . $operator . $condition_value;
		}
		return join(';', $html_conditions);
	}

	//---------------------------------------------------------------------------------------- typeOf
	/**
	 * @param $property      Reflection_Property
	 * @param $property_name string
	 * @return Type
	 */
	protected function typeOf(Reflection_Property $property, string $property_name) : Type
	{
		return $property->getFinalClass()->getProperty($property_name)->getType();
	}

}
