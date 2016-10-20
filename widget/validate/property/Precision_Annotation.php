<?php
namespace SAF\Framework\Widget\Validate\Property;

use SAF\Framework\Reflection;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Widget\Validate\Result;

/**
 * The precision annotation validator
 */
class Precision_Annotation extends Reflection\Annotation
{
	use Annotation;

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * @return string
	 */
	public function reportMessage()
	{
		if (strlen($this->value)) {
			switch ($this->valid) {
				case Result::INFORMATION: return 'precision is respected';
				case Result::WARNING:     return 'precision overflow';
				case Result::ERROR:       return 'precision overflow';
			}
		}
		return '';
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * Validates the property value within this object context
	 *
	 * @param $object object
	 * @return boolean
	 */
	public function validate($object)
	{
		if ($this->property instanceof Reflection_Property) {
			if ($this->value) {
				$value = $this->property->getValue($object);
				return (strlen(rParse($value, '.')) <= $this->value);
			}
			return true;
		}
		return null;
	}

}
