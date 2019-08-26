<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Locale;
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

	//------------------------------------------------------------------------------- $report_message
	/**
	 * @var string
	 */
	protected $report_message;

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

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return ($this->property->getType()->isDateTime() && Locale::current())
			? Locale::current()->date_format->format
			: parent::__toString();
	}

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * Gets the last validate() call resulting report message
	 *
	 * @param $report_message string
	 * @return string
	 */
	public function reportMessage($report_message = null)
	{
		if (isset($report_message)) {
			$this->report_message = $report_message;
		}
		return strval($this->report_message);
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * Validates the property value within this object context
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @return boolean true if validated, false if not validated, null if could not be validated
	 */
	public function validate($object)
	{
		if (
			!($this->property instanceof Reflection_Property)
			|| !$this->property->isPublic()
			|| $this->property->isStatic()
			|| Store_Annotation::of($this->property)->isFalse()
		) {
			return null;
		}
		/** @noinspection PhpUnhandledExceptionInspection $property is always valid for $object */
		$value = $this->property->getValue($object);
		// allowed null
		if (is_null($value) && Null_Annotation::of($this->property)->value) {
			return true;
		}
		$type = $this->property->getType();
		// []
		if ($type->isMultiple() && !is_array($value)) return false;
		// simple
		switch ($type->asString()) {
			case Type::INTEGER: return isStrictNumeric($value, false); break;
			case Type::FLOAT:   return isStrictNumeric($value);        break;
			case Type::STRING:  return is_string($value);              break;
		}
		// object|object[]
		if ($type->isClass()) {
			$class_name = $type->getElementTypeAsString();
			// object[]
			if ($type->isMultiple()) {
				foreach ($value as $object) {
					if (!is_a($object, $class_name)) {
						return false;
					}
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
					|| is_a($value, $class_name);
			}
		}
		// string[]
		elseif ($type->isMultipleString()) {
			foreach ($value as $string) {
				if (!is_string($string)) {
					return false;
				}
			}
		}
		// other cases are not tested : valid is the default
		return true;
	}

}
