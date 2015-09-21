<?php
namespace SAF\Framework\Widget\Validate\Property;

use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Template\Property_Validator;
use SAF\Framework\Reflection\Interfaces;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Widget\Validate\Validate;

/**
 * The length annotation validator
 */
class Length_Annotation extends Annotation implements Property_Validator
{
	use Property_Validate_Annotation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Interfaces\Reflection_Property
	 */
	public function __construct($value, Interfaces\Reflection_Property $property)
	{
		/** @noinspection PhpUndefinedMethodInspection @extends Annotation */
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
				return 'length is !' . $this->value . '!';
			case Validate::WARNING:
			case Validate::ERROR:
				return 'must be !' . $this->value . '! length';
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
		if ($this->property instanceof Reflection_Property) {
			$this->valid = $this->mandatoryAnnotation()->isEmpty($object)
				|| (strlen($this->property->getValue($object)) == $this->value);
		}
		else {
			$this->valid = null;
		}
		return $this->valid;
	}

}
