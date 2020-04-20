<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Template;
use ITRocks\Framework\Reflection\Annotation\Template\Constant_Or_Type_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Do_Not_Inherit;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

/**
 * The installation of the features will install this included feature
 */
class Feature_Include_Annotation extends Constant_Or_Type_Annotation implements Do_Not_Inherit
{
	use Template\Feature_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'feature_include';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $class Reflection_Class The contextual Reflection_Class object
	 */
	public function __construct($value, Reflection_Class $class)
	{
		if (static::$context) {
			$class = static::$context;
		}
		parent::__construct($value, $class);
	}

}
