<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Feature\Validate\Result;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Reflection_Property;

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
	 * @param $value    bool|string|null
	 * @param $property Interfaces\Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct(bool|string|null  $value, Interfaces\Reflection_Property $property)
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
	public function reportMessage() : string
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @return ?boolean true if validated, false if not validated, null if could not be validated
	 */
	public function validate(object $object) : ?bool
	{
		/** @noinspection PhpUnhandledExceptionInspection $this->property from $object and accessible */
		return ($this->property instanceof Reflection_Property)
			? (
			Mandatory_Annotation::of($this->property)->isEmpty($object)
				|| (strlen($this->property->getValue($object)) == $this->value)
			)
			: null;
	}

}
