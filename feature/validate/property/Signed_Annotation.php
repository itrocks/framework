<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Feature\Validate\Result;
use ITRocks\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * The signed annotation validator
 */
class Signed_Annotation extends Boolean_Annotation implements Property_Context_Annotation
{
	use Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'signed';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    ?string
	 * @param $property Interfaces\Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct(?string $value, Interfaces\Reflection_Property $property)
	{
		parent::__construct($value);
		$this->property = $property;
	}

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * @return string
	 */
	public function reportMessage() : string
	{
		if (is_bool($this->value)) {
			switch ($this->valid) {
				case Result::INFORMATION: return 'number signature is conform';
				case Result::WARNING:     return 'number signature not expected';
				case Result::ERROR:       return 'number signature not allowed';
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
	 * @return ?boolean
	 */
	public function validate(object $object) : ?bool
	{
		if ($this->property instanceof Reflection_Property) {
			if (!$this->value) {
				/** @noinspection PhpUnhandledExceptionInspection $property from $object and accessible */
				$value = $this->property->getValue($object);
				return $value >= 0;
			}
			return true;
		}
		return null;
	}

}
