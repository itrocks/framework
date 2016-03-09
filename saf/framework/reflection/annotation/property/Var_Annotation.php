<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Reflection\Annotation\Template\Documented_Type_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Types_Annotation;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Describes the data type of the property.
 *
 * Only values of that type should be stored into the property.
 * If no @var ... annotation is set, the default property is guessed knowing its default value.
 * It is highly recommended to set the @var ... annotation for all business classes properties.
 */
class Var_Annotation extends Documented_Type_Annotation implements Property_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 *
	 * @param $value               string
	 * @param $reflection_property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $reflection_property)
	{
		parent::__construct($value, $reflection_property);
		if (!$this->value) {
			$types       = $reflection_property->getDeclaringClass()->getDefaultProperties();
			$this->value = gettype($types[$reflection_property->getName()]);
		}
	}

}
