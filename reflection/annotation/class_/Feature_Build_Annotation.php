<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Template;
use ITRocks\Framework\Reflection\Annotation\Template\Class_Context_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Do_Not_Inherit;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

/**
 * The installation of the features will add a trait to a class
 *
 * The property $value contains a list of classes :
 * - the first is the class to extend
 * - the next ones are the traits / interfaces used to extend the class
 *
 * @override $value @var string[]
 * @property string[] value
 */
class Feature_Build_Annotation extends Template\List_Annotation
	implements Class_Context_Annotation, Do_Not_Inherit
{
	use Template\Feature_Annotation;
	use Types_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'feature_build';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @example
	 * Class_Name + Interface_Or_Trait_Name_1, IT_2 <=> Class_Name, Interface_Or_Trait_Name, IT_2
	 * @example
	 * If in class :
	 * Interface_Or_Trait_Name <=> Current_class, Interface_Or_Trait_Name
	 * @example
	 * If not in class :
	 * Interface_Or_Trait_Name <=> Interface_Or_Trait_Name's @extends, Interface_Or_Trait_Name
	 * @example
	 * If in interface or trait :
	 * Class_Name <=> Class_Name, Current_Interface_Or_Trait_Name
	 * @param $value string
	 * @param $class Reflection_Class
	 */
	public function __construct($value, Reflection_Class $class)
	{
		if (static::$context) {
			$class = static::$context;
		}
		if (strpos($value, '+')) {
			$value = str_replace('+', ',', $value);
		}
		elseif ($value && $class->isClass()) {
			$value = BS . $class->getName() . ',' . $value;
		}
		parent::__construct($value);
	}

}
