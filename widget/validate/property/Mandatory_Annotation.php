<?php
namespace SAF\Framework\Widget\Validate\Property;

use SAF\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Widget\Validate\Result;

/**
 * The mandatory annotation validator
 */
class Mandatory_Annotation extends Boolean_Annotation
{
	use Annotation;

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * Returns true if the object property is empty
	 *
	 * @param $object object
	 * @return boolean
	 */
	public function isEmpty($object)
	{
		if ($this->property instanceof Reflection_Property) {
			$value = $this->property->getValue($object);
			return $this->property->isValueEmpty($value);
		}
		else {
			return false;
		}
	}

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * @return string
	 */
	public function reportMessage()
	{
		if (is_bool($this->value)) {
			switch ($this->valid) {
				case Result::INFORMATION: return 'mandatory and set';
				case Result::WARNING:     return 'should be filled in';
				case Result::ERROR:       return 'mandatory';
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
		return ($this->property instanceof Reflection_Property)
			? ($this->value ? !$this->isEmpty($object) : true)
			: null;
	}

}
