<?php
namespace ITRocks\Framework\Widget\Validate\Property;

use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Widget\Validate\Result;

/**
 * The length annotation validator
 */
class Length_Annotation extends Reflection\Annotation implements Property_Context_Annotation
{
	use Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'length';

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
			Mandatory_Annotation::of($this->property)->isEmpty($object)
				|| (strlen($this->property->getValue($object)) == $this->value)
			)
			: null;
	}

}
