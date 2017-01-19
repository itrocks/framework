<?php
namespace ITRocks\Framework\Widget\Validate\Property;

use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Property\Null_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;

/**
 * The var annotation validator
 */
class Var_Annotation extends Reflection\Annotation\Property\Var_Annotation
{
	use Annotation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 *
	 * @param $value    string
	 * @param $property Interfaces\Reflection_Property
	 */
	public function __construct($value, Interfaces\Reflection_Property $property)
	{
		parent::__construct($value, $property);
		$this->property = $property;
	}

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * Gets the last validate() call resulting report message
	 *
	 * @return string
	 */
	public function reportMessage()
	{
		return '';
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * Validates the property value within this object context
	 *
	 * @param $object object
	 * @return boolean true if validated, false if not validated, null if could not be validated
	 */
	public function validate($object)
	{
		if ($this->property instanceof Reflection_Property) {
			$value = $this->property->getValue($object);
			// allowed null
			if (is_null($value) && Null_Annotation::of($this->property)->value) {
				return true;
			}
			$type = $this->property->getType();
			// []
			if ($type->isMultiple() && !is_array($value)) return false;
			// simple
			// TODO NORMAL Remove 'is_null()' patch.
			switch ($type->asString()) {
				case Type::INTEGER: return is_null($value) || isStrictNumeric($value, false); break;
				case Type::FLOAT:   return is_null($value) || isStrictNumeric($value);        break;
				case Type::STRING:  return is_null($value) || is_string($value);              break;
			}
			// object|object[]
			if ($type->isClass()) {
				$class_name = $type->getElementTypeAsString();
				// object[]
				if ($type->isMultiple()) {
					foreach ($value as $object) {
						if (!is_a($object, $class_name, true)) return false;
					}
					return true;
				}
				// object
				else {
					return
						// - accepts null if not mandatory
						(is_null($value) && !Mandatory_Annotation::of($this->property)->value)
						// - accepts a string if @store allows a string
						|| (is_string($value) && Store_Annotation::of($this->property)->isString())
						// - accepts an object if is an instance of the class
						|| is_a($value, $class_name, true);
				}
			}
			// string[]
			elseif ($type->isMultipleString()) {
				foreach ($value as $string) {
					if (!is_string($string)) return false;
				}
			}
			// other cases are not tested : validate is the default
			return true;
		}
		return null;
	}

}
