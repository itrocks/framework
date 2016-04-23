<?php
namespace SAF\Framework\Checker\Report\Line;

use SAF\Framework\Checker\Report\Line;
use SAF\Framework\Reflection;
use SAF\Framework\Reflection\Reflection_Property;

/**
 * Check report line
 *
 * @deprecated see Object_Validator
 */
class Annotation implements Line
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	public $property;

	//----------------------------------------------------------------------------------- $annotation
	/**
	 * @var Reflection\Annotation
	 */
	public $annotation;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var mixed
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property   Reflection_Property
	 * @param $annotation Reflection\Annotation
	 * @param $value      mixed
	 */
	public function __construct(
		Reflection_Property $property = null, Reflection\Annotation $annotation = null, $value = null
	) {
		if (isset($property))   $this->property = $property;
		if (isset($annotation)) $this->annotation = $annotation;
		if (isset($value))      $this->value = $value;
	}

}
