<?php
namespace SAF\Framework;

/**
 * Tells a method name that is the getter for that property.
 *
 * The getter will be called each time the program accesses the property.
 * When there is a @link annotation and no @getter, a defaut @getter is set with the Dao access
 * common method depending on the link type.
 */
class Getter_Annotation extends Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value               string
	 * @param $reflection_property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $reflection_property)
	{
		parent::__construct($value);
		if (empty($this->value)) {
			$link = ($reflection_property->getAnnotation("link")->value);
			if (!empty($link)) {
				$this->value = "Aop::get" . $link;
			}
		}
	}

}
