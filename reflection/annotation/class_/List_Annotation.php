<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Template\Class_Context_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Options_Properties_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_\Representative;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

/**
 * Class annotation @list [lock] property1[, property2[, etc]]
 *
 * Indicates which property we want by default for the list controller on the class
 * If lock is set, the user can not customize its list by adding / removing columns
 */
class List_Annotation extends Options_Properties_Annotation implements Class_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'list';

	//------------------------------------------------------------------------------------------ LOCK
	const LOCK = 'lock';

	//-------------------------------------------------------------------------------- RESERVED_WORDS
	const RESERVED_WORDS = [self::LOCK];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor : the default value is #Representative
	 *
	 * @param $value ?string
	 * @param $class Reflection_Class The contextual Reflection_Class object
	 */
	public function __construct(?string $value, Reflection_Class $class)
	{
		if ($value) {
			parent::__construct($value);
			return;
		}
		$this->properties = Representative::of($class)->values;
		$this->value      = [];
	}

}
