<?php
namespace SAF\Framework\Widget\Validate\Property;

use SAF\Framework\Reflection;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Widget\Validate\Result;

/**
 * The length annotation validator
 */
class Length_Annotation extends Reflection\Annotation
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
		if (strlen($this->value)) {
			switch ($this->valid) {
				case Result::WARNING: return 'should be !' . $this->value . '! length';
				case Result::ERROR:   return 'must be !'   . $this->value . '! length';
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
		return ($this->property instanceof Reflection_Property)
			? (
				$this->mandatoryAnnotation()->isEmpty($object)
				|| (strlen($this->property->getValue($object)) == $this->value)
			)
			: null;
	}

}
