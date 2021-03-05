<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\Documented_Type_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Type;

/**
 * Describes the data type of the property.
 *
 * Only values of that type should be stored into the property.
 * If no @var ... annotation is set, the default property is guessed knowing its default value.
 * It is highly recommended to set the @var ... annotation for all business classes properties.
 */
class Var_Annotation extends Documented_Type_Annotation implements Property_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'var';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value               string
	 * @param $reflection_property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $reflection_property)
	{
		parent::__construct($value);
		if (!$this->value) {
			$types       = $reflection_property->getDeclaringClass()->getDefaultProperties();
			$this->value = gettype($types[$reflection_property->getName()]);
		}
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * @return Type
	 */
	public function getType() : Type
	{
		return new Type($this->value . ($this->documentation ? ('|' . $this->documentation) : ''));
	}

}
