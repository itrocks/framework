<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Feature\Validate\Result;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Property\Null_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * The max length annotation validator
 */
class Max_Length_Annotation extends Reflection\Annotation implements Property_Context_Annotation
{
	use Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'max_length';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    bool|string|null
	 * @param $property Interfaces\Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct(bool|string|null $value, Interfaces\Reflection_Property $property)
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
				case Result::INFORMATION:
					return 'length is lesser than !' . $this->value . '!';
				case Result::WARNING:
				case Result::ERROR:
					return 'maximal length is !' . $this->value . '!';
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
		if ($this->property instanceof Reflection_Property) {
			/** @noinspection PhpUnhandledExceptionInspection $property from $object and accessible */
			$value = $this->property->getValue($object);
			return is_null($this->value)
				|| Mandatory::of($this->property)->isEmpty($object)
				|| (is_null($value) && Null_Annotation::of($this->property)->value)
				|| (strlen($value) <= $this->value);
		}
		return null;
	}

}
