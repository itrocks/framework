<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

/**
 * Type can be stored into a constant too : parse it in this case
 */
class Constant_Or_Type_Annotation extends Type_Annotation implements Class_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $class Reflection_Class The contextual Reflection_Class object
	 */
	public function __construct($value, Reflection_Class $class)
	{
		if (strpos($value, '::')) {
			[$scope, $constant_name] = explode('::', $value);
			if (in_array($scope, ['self', 'static'])) {
				$value = BS . $class->getConstant($constant_name);
			}
		}
		parent::__construct($value);
	}

}
