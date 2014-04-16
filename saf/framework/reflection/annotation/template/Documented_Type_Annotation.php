<?php
namespace SAF\Framework\Reflection\Annotation\Template;

use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Method;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Tools\Namespaces;

/**
 * This stores @annotation type and a documentation into two available annotation properties :
 * - $value stores the type name as a string (ie 'string', 'Class_Name' or 'Class_Name[]')
 * - $documentation stores the documentation text, if set
 */
class Documented_Type_Annotation extends Annotation
{

	//-------------------------------------------------------------------------------- $documentation
	/**
	 * Documentation associated to the value type
	 *
	 * @var string
	 */
	public $documentation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Annotation string value is a value type separated from a a documentation with a single space
	 *
	 * @example '@var Class_Name A documentation text can come after that'
	 * @param $value string
	 * @param $class Reflection_Class|Reflection_Method|Reflection_Property
	 */
	public function __construct($value, $class)
	{
		$values = explode(SP, $value);
		parent::__construct($values[0]);
		$this->documentation = trim(substr($value, strlen($values[0])));
		if (!empty($this->value)) {
			if ($this->value[0] === BS) {
				$this->value = substr($this->value, 1);
			}
			if (ctype_upper($this->value[0]) && !strpos($this->value, BS)) {
				$this->value = Namespaces::defaultFullClassName(
					$this->value,
					($class instanceof Reflection_Class) ? $class->name: $class->getDeclaringTrait()
				);
			}
		}
	}

}
