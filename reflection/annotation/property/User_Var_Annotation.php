<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Describes the data type of the property, when viewed by the user
 * Default is the value of Var_Annotation
 */
class User_Var_Annotation extends Var_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'user_var';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value               string
	 * @param $reflection_property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $reflection_property)
	{
		parent::__construct($value, $reflection_property);

		if (!$this->value) {
			$user_annotation = Var_Annotation::of($reflection_property);
			foreach (get_object_vars($user_annotation) as $property_name => $value) {
				$this->$property_name = $value;
			}
		}
	}

}
