<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use Attribute;
use ITRocks\Framework\Feature\Validate\Result;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * Tells what is the wished count of characters for the value of the property
 */
#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Length extends Property implements Has_Set_Final
{
	use Annotation;

	//---------------------------------------------------------------------------------------- $value
	public int $value;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(int $value)
	{
		$this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return strval($this->value);
	}

	//--------------------------------------------------------------------------------- reportMessage
	/** Gets the last validate() call resulting report message */
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
				Mandatory::of($this->property)->isEmpty($object)
				|| (strlen($this->property->getValue($object)) == $this->value)
			)
			: null;
	}

}
