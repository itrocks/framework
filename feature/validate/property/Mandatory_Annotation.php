<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Feature\Validate\Result;
use ITRocks\Framework\History\Has_History;
use ITRocks\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * The mandatory annotation validator
 */
class Mandatory_Annotation extends Boolean_Annotation implements Property_Context_Annotation
{
	use Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'mandatory';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Interfaces\Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct($value, Interfaces\Reflection_Property $property)
	{
		if (!isset($value)) {
			$value = $property->getAnnotation('composite')->value
				|| $property->getAnnotation('link_composite')->value;
		}
		parent::__construct($value);
		$this->property = $property;
	}

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * Returns true if the object property value is empty
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @return boolean
	 */
	public function isEmpty($object)
	{
		if ($this->property instanceof Reflection_Property) {
			/** @noinspection PhpUnhandledExceptionInspection $object of class containing $property */
			$value = $this->property->getValue($object);
			return $this->property->isValueEmpty($value) && !($value instanceof Has_History);
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
