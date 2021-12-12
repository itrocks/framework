<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Tools\Namespaces;

/**
 * Use the @store_name annotation to define the name of the storage of objects into the data link
 *
 * If the annotation is not specified by the programmer, a default value is calculated :
 * the lowercase value of the short class name of @set.
 *
 * @example @store_name storage_class_elements
 */
class Store_Name_Annotation extends Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'store_name';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $class Reflection_Class
	 */
	public function __construct($value, Reflection_Class $class)
	{
		parent::__construct(strtolower(strval($value)));
		if (!$this->value) {
			$this->value = strtolower(Namespaces::shortClassName(Set_Annotation::of($class)->value));
			if ($class->isAbstract()) {
				$this->value .= '_view';
			}
		}
	}

}
