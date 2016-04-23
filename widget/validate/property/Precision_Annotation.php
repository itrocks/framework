<?php
namespace SAF\Framework\Widget\Validate\Property;

use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Template\Property_Validator;
use SAF\Framework\Reflection\Interfaces;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Widget\Validate\Validate;

/**
 * The precision annotation validator
 */
class Precision_Annotation extends Annotation implements Property_Validator
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
	 * @return string
	 */
	public function reportMessage()
	{
		if (strlen($this->value)) {
			switch ($this->valid) {
				case Validate::INFORMATION: return 'precision is respected';
				case Validate::WARNING:     return 'precision overflow';
				case Validate::ERROR:       return 'precision overflow';
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
		$this->object = $object;
		if ($this->property instanceof Reflection_Property) {
			if ($this->value) {
				$value = $this->property->getValue($object);
				$this->valid = (strlen(rParse($value, '.')) <= $this->value);
			}
			else {
				$this->valid = true;
			}
		}
		else {
			$this->valid = null;
		}
		return $this->valid;
	}

}
