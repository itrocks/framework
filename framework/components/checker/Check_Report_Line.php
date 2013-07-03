<?php
namespace SAF\Framework;

/**
 * Check report line
 */
class Check_Report_Line
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	public $property;

	//----------------------------------------------------------------------------------- $annotation
	/**
	 * @var Annotation
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
	 * @param $annotation Annotation
	 * @param $value      mixed
	 */
	public function __construct(
		Reflection_Property $property = null, Annotation $annotation = null, $value = null
	) {
		if (isset($property))   $this->property = $property;
		if (isset($annotation)) $this->annotation = $annotation;
		if (isset($value))      $this->value = $value;
	}

}
