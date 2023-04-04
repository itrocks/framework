<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Feature\Validate\Result;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * The precision annotation validator
 */
class Decimals extends Property\Decimals implements Has_Set_Final
{
	use Annotation;

	//--------------------------------------------------------------------------------- reportMessage
	public function reportMessage() : string
	{
		if (strlen($this->value)) {
			switch ($this->valid) {
				case Result::ERROR: case Result::WARNING: return 'precision overflow';
				case Result::INFORMATION:                 return 'precision is respected';
			}
		}
		return '';
	}

	//-------------------------------------------------------------------------------------- validate
	/** Validates the property value within this object context */
	public function validate(object $object) : ?bool
	{
		if (!($this->property instanceof Reflection_Property)) {
			return null;
		}
		if (!$this->value) {
			return true;
		}
		/** @noinspection PhpUnhandledExceptionInspection $property from object and accessible */
		$value = $this->property->getValue($object);
		return (strlen(rParse(strval($value), DOT)) <= $this->value);
	}

}
