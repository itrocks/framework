<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Template\Options_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Use a specific HTML builder class to build output / edit / object for write for the property
 */
class Widget_Annotation extends Annotation implements Property_Context_Annotation
{
	use Options_Annotation;
	use Types_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'widget';

	//------------------------------------------------------------------------------------------ SORT
	const SORT = 'sort';

	//----------------------------------------------------------------------------------- __construct
	public function __construct(?string $value, Reflection_Property $property)
	{
		$this->constructOptions($value);
		parent::__construct($value);
		if ($this->value) {
			return;
		}
		$type = $property->getType();
		if (!$type->isClass() || $type->isAbstractClass()) {
			return;
		}
		$widget_annotation = Class_\Widget_Annotation::of($type->asReflectionClass());
		if (!$widget_annotation->value) {
			return;
		}
		$this->options = $widget_annotation->options;
		$this->value   = $widget_annotation->value;
	}

}
