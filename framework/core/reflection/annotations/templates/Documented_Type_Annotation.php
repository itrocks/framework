<?php
namespace SAF\Framework;

/**
 * This stores @annotation type and a documentation into two available annotation properties :
 * - $value stores the type name as a string (ie "string", "Class_Name" or "Class_Name[]")
 * - $documentation stores the documentation text, if set
 */
abstract class Documented_Type_Annotation extends Annotation
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
	 * @example "@var Class_Name A documentation text can come after that"
	 * @param $value string
	 */
	public function __construct($value)
	{
		$values = explode(" ", $value);
		parent::__construct($values[0]);
		$this->documentation = trim(substr($value, strlen($values[0])));
	}

}
