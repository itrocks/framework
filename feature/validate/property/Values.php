<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute;

/**
 * Values attribute validator
 */
class Values extends Attribute\Property\Values
{
	use Annotation;

	//--------------------------------------------------------------------------------- $object_value
	/**
	 * @var string|string[]
	 */
	protected array|string $object_value;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		$result = [];
		foreach ($this->values as $value) {
			$result[] = Loc::tr($value);
		}
		if (!$this->property->getAnnotation('ordered_values')->value) {
			sort($result);
		}
		return '[' . join(', ', $result) . ']';
	}

	//--------------------------------------------------------------------------------- reportMessage
	/** Gets the last validate() call resulting report message */
	public function reportMessage() : string
	{
		return 'unauthorized value';
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * Validates the property value within this object context
	 *
	 * @param $object object
	 * @return boolean true if validated, false if not validated, null if it could not be validated
	 */
	public function validate(object $object) : bool
	{
		$values = $this->values;
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
