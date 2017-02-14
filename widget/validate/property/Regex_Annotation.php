<?php
namespace ITRocks\Framework\Widget\Validate\Property;

use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Widget\Validate\Result;

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

	//------------------------------------------------------------------------------- REGEX_DELIMITER
	const REGEX_DELIMITER = SL;

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
	public function getValue(){
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
				case Result::ERROR:   return 'has invalid format';
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

		//fix pattern with a delimiter, if pattern not set
		if ($pattern[0] != $pattern[strlen($pattern)-1] && $pattern[0] != $pattern[strlen($pattern)-2]){
			$pattern = self::REGEX_DELIMITER . $pattern . self::REGEX_DELIMITER;
		}
		return ($this->property instanceof Reflection_Property)
			? (
			(preg_match($pattern, $this->property->getValue($object)) == 1) ? true : false
			)
			: null;

	}

}
