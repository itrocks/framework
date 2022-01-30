<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Interfaces;

/**
 * Values annotation validator
 */
class Values_Annotation extends Reflection\Annotation\Property\Values_Annotation
{
	use Annotation;

	//--------------------------------------------------------------------------------- $object_value
	/**
	 * @var string
	 */
	protected $object_value;

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
		$result = [];
		foreach ($this->value as $value) {
			$result[] = Loc::tr($value);
		}
		if (!$this->property->getAnnotation('ordered_values')->value) {
			sort($result);
		}
		return '[' . join(', ', $result) . ']';
	}

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * Gets the last validate() call resulting report message
	 *
	 * @return string
	 */
	public function reportMessage()
	{
		return 'unauthorized value';
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
		$values = $this->value;
		if (!$values) {
			return true;
		}
		$this->object_value = $this->property->getValue($object);
		if (!$this->object_value) {
			return true;
		}
		if (is_array($this->object_value)) {
			foreach ($this->object_value as $value) {
				if (!in_array($value, $values)) {
					return false;
				}
			}
			return true;
		}
		return in_array($this->object_value, $values);
	}

}
