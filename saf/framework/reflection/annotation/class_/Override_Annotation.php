<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\Reflection\Annotation\Template\Annotation_In;
use SAF\Framework\Reflection\Annotation\Template\List_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Multiple_Annotation;

/**
 * Class override annotation
 */
class Override_Annotation extends List_Annotation implements Multiple_Annotation
{
	use Annotation_In;

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
