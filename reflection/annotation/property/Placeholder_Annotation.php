<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection;

/**
 * A Placeholder is a substitute expression helping to know what content to fill in a field
 * Value of annotation shouls be a method name for dynamic result or a string for static text
 *
 * @example
 * * @placeholder my text that will be translated
 * * @placeholder myMethodName
 * * @placeholder self::myStaticMethodName
 * * @placeholder Class_Name::anotherMethodName
 */
class Placeholder_Annotation extends Method_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'placeholder';

	//------------------------------------------------------------------------------------ $is_method
	/**
	 * @var boolean
	 */
	private $is_method;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           string
	 * @param $class_property  Reflection
	 * @param $annotation_name string
	 */
	public function __construct($value, Reflection $class_property, $annotation_name)
	{
		parent::__construct($value, $class_property, $annotation_name);

		if (($pos = strpos($this->value, '::')) !== false) {
			$class_name  = BS . substr($this->value, 0, $pos);
			$method_name = substr($this->value, $pos + 2);
			// value is a method
			if (method_exists($class_name, $method_name)) {
				$this->is_method = true;
				// value is not callable (private or protected?)
				if (!is_callable([$class_name, $method_name])) {
					$this->value = '';
					trigger_error(
						"method $class_name::$method_name is not callable. Please review.", E_USER_ERROR
					);
				}
			}
			// value is a string to display prefixed by a class name to remove (method_name is the string)
			else {
				$this->is_method = false;
				$this->value     = $method_name;
			}
		}
		// value is a string to display
		else {
			$this->is_method = false;
		}
	}



	//-------------------------------------------------------------------------------------- isMethod
	/**
	 * @return boolean
	 */
	public function isMethod()
	{
		return $this->is_method;
	}

}
