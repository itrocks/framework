<?php
namespace ITRocks\Framework\Checker\Report\Line;

use ITRocks\Framework\Checker\Report\Line;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * Check report line
 *
 * @deprecated see Validator
 */
class Annotation implements Line
{

	//----------------------------------------------------------------------------------- $annotation
	/**
	 * @var Reflection\Annotation
	 */
	public $annotation;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	public $property;

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
