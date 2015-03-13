<?php
namespace SAF\Framework\Widget\Validate\Property;

use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Template\Validator;
use SAF\Framework\Reflection\Interfaces;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Widget\Validate\Validate;

/**
 * The min length annotation validator
 */
class Min_Length_Annotation extends Annotation implements Validator
{
	use Validate_Annotation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Interfaces\Reflection_Property
	 */
	public function __construct($value, Interfaces\Reflection_Property $property)
	{
		parent::__construct($value);
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
		switch ($this->valid) {
			case Validate::INFORMATION:
				return 'length is greater than !' . $this->value . '!';
			case Validate::WARNING:
			case Validate::ERROR:
				return 'minimal length is !' . $this->value . '!';
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
		$this->object = $object;
		$this->valid = ($this->property instanceof Reflection_Property)
			? $this->valid = ($this->property->getValue($object) >= $this->value)
			: null;
		return $this->valid;
	}

}
