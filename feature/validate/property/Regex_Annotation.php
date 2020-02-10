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
	 * @param $value    string
	 * @param $property Interfaces\Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct($value, Interfaces\Reflection_Property $property)
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
	public function getValue()
	{
		return null;
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
				case Result::ERROR: return 'has invalid format';
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
		$pattern = $this->value;
		if (strpos(substr($pattern, -2), $pattern[0]) === false) {
			foreach (static::REGEX_DELIMITERS as $delimiter) {
				if (strpos($pattern, $delimiter) === false) {
					$pattern = $delimiter . $pattern . $delimiter;
					break;
				}
			}
		}
		$value = $this->property->getValue($object);
		return (bool)preg_match($pattern, $value);
	}

}
