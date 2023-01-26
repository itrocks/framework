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

	//----------------------------------------------------------------------------------- $calculated
	/**
	 * Every Class has a @store_name. It can be set explicitly or automatically calculated, if there
	 * is no explicit annotation.
	 * $calculated is true if there was no explicit @store_name annotation for the class
	 */
	public bool $calculated = false;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(?string $value, Reflection_Class $class)
	{
		parent::__construct(strtolower(strval($value)));
		if ($this->value) {
			return;
		}
		$this->calculated = true;
		$this->value = strtolower(Namespaces::shortClassName(Set_Annotation::of($class)->value));
		if ($class->isAbstract()) {
			$this->value .= '_view';
		}
	}

}
