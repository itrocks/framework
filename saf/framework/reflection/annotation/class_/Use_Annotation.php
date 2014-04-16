<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\Reflection\Annotation\Template\List_Annotation;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Tools\Namespaces;

/**
 * This must be used for interfaces that are designed to use a given class
 * TODO NORMAL is it used at this time ?
 */
class Use_Annotation extends List_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $class Reflection_Class
	 */
	public function __construct($value, Reflection_Class $class)
	{
		parent::__construct($value);
		foreach ($this->values() as $key => $value) {
			if ($value[0] === BS) {
				$this->value[$key] = substr($value, 1);
			}
			if (!strpos($value, BS)) {
				$this->value[$key] = Namespaces::defaultFullClassName($value, $class->name);
			}
		}
	}

}
