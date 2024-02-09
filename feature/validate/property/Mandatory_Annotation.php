<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use Error;
use ITRocks\Framework\Feature\History\Has_History;
use ITRocks\Framework\Feature\Validate\Result;
use ITRocks\Framework\Reflection\Annotation\Property;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * The mandatory annotation validator
 */
class Mandatory_Annotation extends Property\Mandatory_Annotation
{
	use Annotation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    bool|string|null
	 * @param $property Interfaces\Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct(bool|string|null $value, Interfaces\Reflection_Property $property)
	{
		parent::__construct($value, $property);
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
	public function isEmpty(object $object) : bool
	{
		if ($this->property instanceof Reflection_Property) {
			try {
				/** @noinspection PhpUnhandledExceptionInspection $object of class containing $property */
				$value = $this->property->getValue($object);
			}
			catch (Error) {
				// if uninitialized property value error, then it is empty
				return true;
			}
			return $this->property->isValueEmpty($value) && !($value instanceof Has_History);
		}
		return false;
	}

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * @return string
	 */
	public function reportMessage() : string
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
	public function validate(object $object) : bool
	{
		return !$this->value || !$this->isEmpty($object);
	}

}
