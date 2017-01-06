<?php
namespace ITRocks\Framework\Widget\Validate\Property;

use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Property\Null_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Widget\Validate\Result;

/**
 * The var annotation validator
 */
class Var_Annotation extends Reflection\Annotation\Property\Var_Annotation
{
	use Annotation;

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
			// null
			if (Null_Annotation::of($this->property)->value && is_null($value)) {
				return true;
			}
			$type = $this->property->getType();
			// []
			if ($type->isMultiple() && !is_array($value)) return false;
			// simple
			switch ($type->asString()) {
				case Type::INTEGER: return isStrictNumeric($value, false); break;
				case Type::FLOAT:   return isStrictNumeric($value); break;
				case Type::STRING:  return is_string($value); break;
			}
			// object|object[]
			if ($type->isClass()) {
				$class_name = $type->getElementTypeAsString();
				if ($type->isMultiple()) {
					// object[]
					foreach ($value as $object) {
						if (!is_object($object) || !is_a($object, $class_name, true)) return false;
					}
					return true;
				}
				else {
					// object
					if (!is_object($value) || !is_a($value, $class_name, true)) return false;
				}
			}
			// string[]
			elseif ($type->isMultipleString()) {
				foreach ($value as $string) {
					if (!is_string($string)) return false;
				}
			}
			return true;
		}
		return null;
	}

}
