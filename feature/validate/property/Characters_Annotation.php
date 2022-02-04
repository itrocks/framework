<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Feature\Validate\Result;
use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces;

/**
 * The authorized characters annotation for validator
 */
class Characters_Annotation extends List_Annotation implements Property_Context_Annotation
{
	use Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'characters';

	//----------------------------------------------------------------------------------- $user_value
	/**
	 * @var string
	 */
	protected string $user_value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    ?string
	 * @param $property Interfaces\Reflection_Property
	 */
	public function __construct(?string $value, Interfaces\Reflection_Property $property)
	{
		parent::__construct($value);
		$this->property = $property;
	}

	//------------------------------------------------------------------------- firstInvalidCharacter
	/**
	 * @return string
	 */
	protected function firstInvalidCharacter() : string
	{
		$length = strlen($this->user_value);
		for ($i = 0; $i < $length; $i ++) {
			$character = $this->user_value[$i];
			if (!$this->match($character)) {
				return $character;
			}
		}
		return '?';
	}

	//----------------------------------------------------------------------------------------- match
	/**
	 * @param $value string
	 * @return boolean
	 */
	protected function match(string $value) : bool
	{
		$pattern = '';
		foreach ($this->values() as $character) {
			$pattern .= (strlen($character) === 1) ? (BS . $character) : $character;
		}
		$pattern = '^[' . $pattern . ']+$';
		foreach (Regex_Annotation::REGEX_DELIMITERS as $delimiter) {
			if (!str_contains($pattern, $delimiter)) {
				$pattern = $delimiter . $pattern . $delimiter;
				break;
			}
		}

		return (bool)preg_match($pattern, $value);
	}

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * Gets the last validate() call resulting report message
	 *
	 * @return string
	 */
	public function reportMessage() : string
	{
		if ($this->value && ($this->valid === Result::ERROR)) {
			return 'has an invalid character !' . $this->firstInvalidCharacter() . '!';
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
	public function validate(object $object) : bool
	{
		$this->user_value = $this->property->getValue($object);
		return $this->match($this->user_value);
	}

}
