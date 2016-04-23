<?php
namespace SAF\Framework\Reflection\Annotation\Template;

use SAF\Framework\Reflection\Annotation;

/**
 * This stores @annotation type and a documentation into two available annotation properties :
 * - $value stores the type name as a string (ie 'string', 'Class_Name' or 'Class_Name[]')
 * - $documentation stores the documentation text, if set
 */
class Documented_Type_Annotation extends Annotation
{
	use Types_Annotation;

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
	 * @example '@var An_Array[]|Another[]|array Documentation will begin at "Another[]"'
	 * @param $value string
	 */
	public function __construct($value)
	{
		$i = strpos($value, SP);
		$j = strpos($value, '|');
		if (($i === false) || ($j !== false) && ($j < $i)) {
			$i = $j;
		}
		if ($i === false) {
			parent::__construct($value);
			$this->documentation = '';
		}
		else {
			parent::__construct(substr($value, 0, $i));
			$this->documentation = trim(substr($value, $i + 1));
		}
	}

}
