<?php
namespace SAF\Framework;

/**
 * Class override annotation
 */
class Class_Override_Annotation extends List_Annotation implements Multiple_Annotation
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class
	 */
	public $class;

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var string
	 */
	public $property_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $class Reflection_Class
	 */
	public function __construct($value, Reflection_Class $class)
	{
		$this->class = $class;
		foreach (explode(' @', $value) as $override_annotation) {
			if (!isset($this->property_name)) {
				$this->property_name = $override_annotation;
			}
			else {
				if (substr_count($override_annotation, SP)) {
					list($annotation_name, $annotation_value) = explode(SP, $override_annotation, 2);
				}
				else {
					$annotation_name = $override_annotation;
					$annotation_value = '';
				}
				$this->value[$annotation_name] = $annotation_value;
			}
		}
	}

}
