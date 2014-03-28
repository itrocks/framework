<?php
namespace SAF\Framework;

/**
 * Describes the data type of the property.
 *
 * Only values of that type should be stored into the property.
 * If no @var annotation is set, the default property is guessed knowing it's default value.
 * It is highly recommended to set the @var annotation for all business classes properties.
 */
class Var_Annotation extends Documented_Type_Annotation implements Property_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 *
	 * @param $value string
	 * @param $reflection_object Reflection_Property
	 */
	public function __construct($value, Reflection_Property $reflection_object)
	{
		parent::__construct($value);
		if (!$this->value) {
			$types = $reflection_object->getDeclaringClass()->getDefaultProperties();
			$this->value = gettype($types[$reflection_object->name]);
		}
	}

}
