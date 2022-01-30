<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Class_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Tools\Names;

/**
 * Display annotation
 */
class Display_Annotation extends Annotation implements Class_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value ?string
	 * @param $class Reflection_Class
	 */
	public function __construct(?string $value, Reflection_Class $class)
	{
		parent::__construct($value);
		if (!$this->value) {
			$this->value = str_replace('_', SP, Names::setToSingle(Store_Name_Annotation::of($class)));
		}
	}

}
