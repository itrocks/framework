<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Reflection\Annotation;

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
	public string $documentation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Annotation string value is a value type separated from a a documentation with a single space
	 *
	 * @example '@var Class_Name A documentation text can come after that'
	 * @example '@var An_Array[]|Another[]|array Documentation will begin at "Another[]"'
	 * @param $value ?string
	 */
	public function __construct(?string $value)
	{
		$value = strval($value);
		if ($add_null = str_starts_with($value, '?')) {
			$value = substr($value, 1);
		}
		$position     = strpos($value, SP);
		$end_position = strpos($value, '|');
		if (($position === false) || ($end_position !== false) && ($end_position < $position)) {
			$position = $end_position;
		}
		/** @noinspection PhpStrictComparisonWithOperandsOfDifferentTypesInspection inspector bug */
		if ($position === false) {
			parent::__construct($value . ($add_null ? '|null' : ''));
			$this->documentation = '';
		}
		else {
			parent::__construct(substr($value, 0, $position) . ($add_null ? '|null' : ''));
			$this->documentation = trim(substr($value, $position + 1));
		}
	}

}
