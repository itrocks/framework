<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Feature\Validate\Result;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * The precision annotation validator
 */
class Precision_Annotation extends Reflection\Annotation implements Property_Context_Annotation
{
	use Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'precision';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Interfaces\Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct($value, Interfaces\Reflection_Property $property)
	{
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @return boolean
	 */
	public function validate($object)
	{
		if ($this->property instanceof Reflection_Property) {
			if ($this->value) {
				/** @noinspection PhpUnhandledExceptionInspection $property from object and accessible */
				$value = $this->property->getValue($object);
				return (strlen(rParse($value, DOT)) <= $this->value);
			}
			return true;
		}
		return null;
	}

}
