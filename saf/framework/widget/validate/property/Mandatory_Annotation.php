<?php
namespace SAF\Framework\Widget\Validate\Property;

use SAF\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Validator;
use SAF\Framework\Reflection\Interfaces;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Widget\Validate\Validate;

/**
 * The mandatory annotation validator
 */
class Mandatory_Annotation extends Boolean_Annotation implements Validator
{
	use Validate_Annotation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Interfaces\Reflection_Property
	 */
	public function __construct($value, Interfaces\Reflection_Property $property)
	{
		/** @noinspection PhpUndefinedMethodInspection */
		parent::__construct($value);
		$this->property = $property;
	}

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * @return string
	 */
	public function reportMessage()
	{
		switch ($this->valid) {
			case true:  return "the mandatory value is set";
			case false: return "the mandatory value is missing";
			default:    return "";
		}
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
				$this->valid = !(is_null($value) || ($value === ''));
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
