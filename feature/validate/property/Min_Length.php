<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use Attribute;
use ITRocks\Framework\Feature\Validate\Result;
use ITRocks\Framework\Reflection\Annotation\Property\Null_Annotation;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * Tells what is the minimal count of characters for the value of the property
 */
#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Min_Length implements Has_Set_Final
{
	use Annotation;
	use Common;

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
				case Result::INFORMATION:
					return 'length is greater than !' . $this->value . '!';
				case Result::WARNING:
				case Result::ERROR:
					return 'minimal length is !' . $this->value . '!';
			}
		}
		return '';
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * Validates the property value within this object context
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return ?boolean true if validated, false if not validated, null if could not be validated
	 */
	public function validate(object $object) : ?bool
	{
		if ($this->property instanceof Reflection_Property) {
			/** @noinspection PhpUnhandledExceptionInspection $property from $object and accessible */
			$value = $this->property->getValue($object);
			return Mandatory::of($this->property)->isEmpty($object)
				|| (is_null($value) && Null_Annotation::of($this->property)->value)
				|| (strlen($value) >= $this->value);
		}
		return null;
	}

}
