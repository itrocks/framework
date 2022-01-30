<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Describes the data type of the property, when viewed by the user
 */
class User_Var_Annotation extends Var_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'user_var';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value               ?string
	 * @param $reflection_property Reflection_Property
	 */
	public function __construct(?string $value, Reflection_Property $reflection_property)
	{
		if ($value) {
			parent::__construct($value, $reflection_property);
		}
		else {
			$this->value = $value;
		}
	}

}
