<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\Builder;
use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Template\Class_Context_Annotation;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Tools\Namespaces;

/**
 * This tells that the class is a link class
 *
 * It means that :
 * - it's data storage set naming will be appended by a '_links'
 * - there will be no data storage field creation for parent linked table into this data storage set
 *   but a link field
 *
 * @example '@link User' means that the herited class of User is linked to the parent class User
 * - data storage fields will be those from this class, and immediate parent classes if they are not 'User'
 * - an additional implicit data storage field will link to the class 'User'
 */
class Link_Annotation extends Annotation implements Class_Context_Annotation
{

	//---------------------------------------------------------------------------------------- $value
	/**
	 * Default annotation constructor receive the full doc text content
	 *
	 * Annotation class will have to parse it ie for several parameters or specific syntax, or if they want to store specific typed or calculated value
	 *
	 * @param $value string
	 * @param $class Reflection_Class
	 */
	public function __construct($value, Reflection_Class $class)
	{
		if ($value[0] === BS) {
			$value = substr($value, 1);
		}
		$this->value = $value
			? Builder::className(Namespaces::defaultFullClassName($value, $class->name))
			: $value;
	}

}
