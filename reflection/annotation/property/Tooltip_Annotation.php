<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection;

/**
 * A Tooltip is a text to help the user to know how to fill in a form field
 *
 * @example @tooltip my text that will be translated
 */
class Tooltip_Annotation extends Method_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'tooltip';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           string
	 * @param $class_property  Reflection
	 * @param $annotation_name string
	 */
	public function __construct($value, Reflection $class_property, $annotation_name)
	{
		parent::__construct($value, $class_property, $annotation_name);
		// value is a string to display prefixed by a class name to remove
		if (($pos = strpos($this->value, '::')) !== false) {
			$string      = substr($this->value, $pos + 2);
			$this->value = $string;
		}
	}

}
