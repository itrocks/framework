<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Template;
use ITRocks\Framework\Reflection\Annotation\Template\Do_Not_Inherit;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * Declares a method to be called during feature installation
 *
 * This method (installFeature is the default value if empty) will be called each time a feature
 * is installed
 */
class Feature_Install_Annotation extends Method_Annotation implements Do_Not_Inherit
{
	use Template\Feature_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'feature_install';

	//---------------------------------------------------------------------------------------- $delay
	/**
	 * If to be executed later : called with a delay of n user clicks
	 *
	 * @var integer
	 */
	public int $delay = 0;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           string
	 * @param $class           Reflection|Reflection_Class The contextual Reflection_Class object
	 * @param $annotation_name string
	 */
	public function __construct($value, Reflection $class, $annotation_name)
	{
		if (strpos($value, SP)) {
			[$value, $delay] = explode(SP, $value);
			$this->delay = intval($delay);
		}
		if (static::$context) {
			$class = static::$context;
		}
		parent::__construct($value, $class, $annotation_name);
	}

}
