<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use Error;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Feature\History\Has_History;
use ITRocks\Framework\Feature\Validate\Result;
use ITRocks\Framework\Reflection\Attribute;
use ITRocks\Framework\Reflection\Attribute\Has_Attributes;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * The mandatory annotation validator
 */
class Mandatory extends Attribute\Property\Mandatory
{
	use Annotation;

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

	//-------------------------------------------------------------------------------------------- of
	/**
	 * @param $reflection Reflection|Has_Attributes
	 * @return static|static[]|null
	 */
	public static function of(Reflection|Has_Attributes $reflection) : array|object|null
	{
		static $recurse = false;
		if ($recurse) {
			return parent::of($reflection);
		}
		$recurse   = true;
		$mandatory = $reflection->getAttribute(Builder::current()->sourceClassName(static::class));
		$recurse   = false;
		return $mandatory;
	}

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * @return string
	 */
	public function reportMessage() : string
	{
		switch ($this->valid) {
			case Result::INFORMATION: return 'mandatory and set';
			case Result::WARNING:     return 'should be filled in';
			case Result::ERROR:       return 'mandatory';
		}
		return '';
	}

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Reflection|Reflection_Property $reflection) : void
	{
		parent::setFinal($reflection);
		$this->property = $reflection;
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
