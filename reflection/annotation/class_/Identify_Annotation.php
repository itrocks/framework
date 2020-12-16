<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

/**
 * The 'identity' annotation stores the list of properties which values identify an object,
 * in functional / business terms.
 * The default value, when not set, is taken from representative
 *
 * @example a property called 'code' could be an identifying property for a unique coded object
 */
class Identify_Annotation extends Representative_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'identify';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $class Reflection_Class
	 */
	public function __construct($value, Reflection_Class $class)
	{
		if (!$value) {
			$value = join(',', Representative_Annotation::of($class)->values());
		}
		parent::__construct($value, $class);
	}

}
