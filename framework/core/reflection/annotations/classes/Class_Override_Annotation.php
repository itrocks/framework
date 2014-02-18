<?php
namespace SAF\Framework;

/**
 * Class override annotation
 */
class Class_Override_Annotation extends List_Annotation implements Multiple_Annotation
{

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var string
	 */
	public $property_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 */
	public function __construct($value)
	{
		foreach (explode(' @', $value) as $override_annotation) {
			if (!isset($this->property_name)) {
				$this->property_name = $override_annotation;
			}
			else {
				if (substr_count($override_annotation, ' ')) {
					list($annotation_name, $annotation_value) = explode(' ', $override_annotation, 2);
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
