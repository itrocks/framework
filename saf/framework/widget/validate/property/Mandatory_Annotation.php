<?php
namespace SAF\Framework\Widget\Validate\Property;

use SAF\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Property_Validator;
use SAF\Framework\Reflection\Interfaces;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Widget\Validate\Validate;

/**
 * The mandatory annotation validator
 */
class Mandatory_Annotation extends Boolean_Annotation implements Property_Validator
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
		switch ($this->valid) {
			case Validate::INFORMATION: return 'mandatory and set';
			case Validate::WARNING:     return 'should be filled in';
			case Validate::ERROR:       return 'mandatory';
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
		$this->object = $object;
		if ($this->property instanceof Reflection_Property) {
			$this->valid = $this->value ? !$this->isEmpty($object) : true;
		}
		else {
			$this->valid = null;
		}
		return $this->valid;
	}

}
