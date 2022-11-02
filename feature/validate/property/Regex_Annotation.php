<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Feature\Validate\Result;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces;

/**
 * The regex annotation validator
 *
 * @override value @user_getter getValue
 */
class Regex_Annotation extends Reflection\Annotation implements Property_Context_Annotation
{
	use Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'regex';

	//------------------------------------------------------------------------------ REGEX_DELIMITERS
	const REGEX_DELIMITERS = [SL, BQ, '~', '¤', 'µ', '§'];

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

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Used from template. We don't want to display pattern format for users
	 *
	 * @return null
	 */
	public function getValue() : mixed
	{
		return null;
	}

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * Gets the last validate() call resulting report message
	 *
	 * @return string
	 */
	public function reportMessage() : string
	{
		return (strlen($this->value) && ($this->valid === Result::ERROR))
			? 'has invalid format'
			: '';
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * Validates the property value within this object context
	 *
	 * @param $object object
	 * @return ?boolean true if validated, false if not validated, null if could not be validated
	 */
	public function validate(object $object) : ?bool
	{
		$pattern = $this->value;
		if (!str_contains(substr($pattern, -2), $pattern[0])) {
			foreach (static::REGEX_DELIMITERS as $delimiter) {
				if (!str_contains($pattern, $delimiter)) {
					$pattern = $delimiter . $pattern . $delimiter;
					break;
				}
			}
		}
		$value = $this->property->getValue($object);
		return (bool)preg_match($pattern, $value);
	}

}
