<?php
namespace ITRocks\Framework\Widget\Validate\Property;

use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Widget\Validate\Result;

/**
 * The min value annotation validator
 */
class Min_Value_Annotation extends Reflection\Annotation
{
	use Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'min_value';

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * Gets the last validate() call resulting report message
	 *
	 * @return string
	 */
	public function reportMessage()
	{
		if (strlen($this->value)) {
			switch ($this->valid) {
				case Result::INFORMATION:
					return 'value is greater than !' . $this->value . '!';
				case Result::WARNING:
				case Result::ERROR:
					return 'minimal value is !' . $this->value . '!';
			}
		}
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
			return $this->mandatoryAnnotation()->isEmpty($object)
				|| (is_null($value) && $this->property->getAnnotation('null')->value)
				|| ($value >= $this->value);
		}
		return null;
	}

}
