<?php
namespace ITRocks\Framework\Feature\Cards;

use ITRocks\Framework\Reflection\Annotation\Template\Class_Context_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

/**
 * Cards property annotation commons
 *
 * Each element of the list annotation is the name of the property, and can be followed by rules
 * separated from the name of the property by a space
 */
abstract class Annotation extends List_Annotation implements Class_Context_Annotation
{

	//---------------------------------------------------------------------- CARD_PROPERTY_CLASS_NAME
	const CARD_PROPERTY_CLASS_NAME = Property::class;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class
	 */
	public Reflection_Class $class;

	//------------------------------------------------------------------------------- $property_names
	/**
	 * @var string[] key = value = name or path of a property
	 */
	public array $property_names = [];

	//---------------------------------------------------------------------------------------- $rules
	/**
	 * @var string[] key is the name (path) of the property, the value describes the rules
	 */
	public array $rules = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $class Reflection_Class
	 */
	public function __construct(string $value, Reflection_Class $class)
	{
		parent::__construct($value);
		$this->class = $class;
		foreach ($this->value as $value) {
			$property_name                        = lParse($value, SP);
			$rules                                = rParse($value, SP);
			$this->property_names[$property_name] = $property_name;
			if (strlen($rules)) {
				$this->addRule($property_name, $rules);
			}
		}
	}

	//--------------------------------------------------------------------------------------- addRule
	/**
	 * Add a rule set for the property name / path
	 *
	 * @param $property_name string property name or path
	 * @param $rules_string  string the rule described into a string
	 */
	protected function addRule(string $property_name, string $rules_string)
	{
		$this->rules[$property_name] = $rules_string;
	}

	//------------------------------------------------------------------------------------ properties
	/**
	 * Return property rules
	 *
	 * @return Property[]
	 */
	public function properties() : array
	{
		$properties = [];
		foreach ($this->rules as $property_name => $rule) {
			$class_name = static::CARD_PROPERTY_CLASS_NAME;
			$properties[$property_name] = new $class_name(
				$this->class->getProperty($property_name), $rule
			);
		}
		return $properties;
	}

}
