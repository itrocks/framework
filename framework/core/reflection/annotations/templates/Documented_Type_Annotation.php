<?php
namespace SAF\Framework;

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
	 * @param string $value
	 */
	public function __construct($value)
	{
		$values = explode(" ", $value);
		parent::__construct($values[0]);
		$this->documentation = trim(substr($value, strlen($values[0])));
	}

}
