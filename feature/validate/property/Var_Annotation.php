<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Loc;
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
	public function __toString() : string
	{
		return ($this->property->getType()->isDateTime() && Locale::current())
			? $this->dateFormat()
			: Loc::tr(parent::__toString());
	}

	//------------------------------------------------------------------------------------ dateFormat
	/**
	 * @return string
	 */
	public function dateFormat() : string
	{
		$date    = 'Y-m-d-H-i-s';
		$date    = array_combine(explode('-', $date), explode('-', date($date)));
		$format  = Locale::current()->date_format->format;
		$format .= ' H:i' . ($this->property->getAnnotation('show_seconds')->value ? ':s' : '');
		$format  = strReplace($date, $format);
		return $format;
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
		foreach (array_merge([$type], $type->alternatives) as $type) {
			// []
			if ($type->isMultiple() && !is_array($value)) {
				return false;
			}
			// simple
			if (in_array(
				$type->asString(), [Type::BOOLEAN, Type::FLOAT, Type::INTEGER, Type::MIXED, Type::STRING]
			)) {
				switch ($type->asString()) {
					case Type::BOOLEAN:
						if (in_array($value, [null, false, true, 0, 1])) return true;
						break;
					case Type::FLOAT:
						if (isStrictNumeric($value)) return true;
						break;
					case Type::INTEGER:
						if (isStrictNumeric($value, false)) return true;
						break;
					case Type::MIXED:
						return true;
					case Type::STRING:
						if (is_null($value) || is_string($value)) return true;
						break;
				}
			}
			// object|object[]
			elseif ($type->isClass()) {
				$class_name = $type->getElementTypeAsString();
				// object[]
				if ($type->isMultiple()) {
					$all_are_ok = true;
					foreach ($value as $object) {
						if (!is_a($object, $class_name)) {
							$all_are_ok = false;
							break;
						}
					}
					if ($all_are_ok) {
						return true;
					}
				}
				// object
				else {
					if (
						// - accepts null if not mandatory
						(is_null($value) && !Mandatory_Annotation::of($this->property)->value)
						// - accepts a string if @store allows a string
						|| (is_string($value) && Store_Annotation::of($this->property)->isString())
						// - accepts an object if is an instance of the class
						|| is_a($value, $class_name)
						// - accepts an object if @var object
						|| (is_object($value) && ($class_name === 'object'))
					) {
						return true;
					}
				}
			}
			// string[]
			elseif ($type->isMultipleString()) {
				$all_are_ok = true;
				foreach ($value as $string) {
					if (!is_string($string)) {
						$all_are_ok = false;
						break;
					}
				}
				if ($all_are_ok) {
					return true;
				}
			}
			// other cases are not tested : valid is the default
			else {
				trigger_error('Untested @var type ' . $type->asString(), E_USER_NOTICE);
				return true;
			}
		}
		return false;
	}

}
