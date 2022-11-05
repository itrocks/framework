<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Type;

/**
 * The values annotation lists the values the property can take.
 *
 * The program should not be able to give the property another value than one of the list.
 * This is useful for data controls on string[], float[] or integer[] properties.
 */
class Values_Annotation extends List_Annotation implements Property_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'values';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    ?string
	 * @param $property Reflection_Property
	 */
	public function __construct(?string $value, Reflection_Property $property)
	{
		parent::__construct($value);
		if (count($this->values()) === 1) {
			if (str_contains($value = reset($this->value), '::')) {
				$this->importValues($value, $property);
			}
		}
		if (isset($value)) {
			$type = $property->getType();
			$function = match($type->getElementTypeAsString()) {
				Type::FLOAT   => 'floatval',
				Type::INTEGER => 'intval',
				default       => 'strval'
			};
			foreach ($this->values() as $key => $value) {
				$this->value[$key] = $function($value);
			}
		}
	}

	//---------------------------------------------------------------------------------- importValues
	/**
	 * @param $from     string
	 * @param $property Reflection_Property
	 */
	private function importValues(string $from, Reflection_Property $property) : void
	{
		[$value, $option]    = str_contains($from, SP) ? explode(SP, $from, 2) : [$from, null];
		[$class_name, $what] = explode(
			'::', (new Method_Annotation($value, $property, 'values'))->value
		);
		$reflection_class = get_class($property->getDeclaringClass());
		/** @var $class Reflection_Class */
		$class = new $reflection_class($class_name);
		// each class const that is a value (not an array) is a value. Option local = no parents const
		if ($what === 'const') {
			$constants = ($option === 'local') ? $class->getConstants([]) : $class->getConstants();
			$this->value = [];
			foreach ($constants as $constant) {
				if (!is_array($constant)) {
					$this->value[] = $constant;
				}
			}
		}
		// a class const that contains an array of values
		elseif (
			ctype_upper(preg_replace('/[^a-zA-Z]+/', '', $what))
			&& is_array($constants = $class->getConstant($what))
		) {
			$this->value = array_values($constants);
		}
		// a static property default array value
		else {
			$what = ltrim($what, '$');
			$defaults = $class->getDefaultProperties();
			if (isset($defaults[$what]) && is_array($defaults[$what])) {
				$this->value = array_values($defaults[$what]);
			}
		}
	}

}
