<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Mapper\Getter;
use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Template\Method_Annotation;
use SAF\Framework\Reflection\Interfaces\Reflection;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Tells a method name that is the getter for that property.
 *
 * The getter will be called each time the program accesses the property.
 * When there is a @link annotation and no @getter, a defaut @getter is set with the Dao access
 * common method depending on the link type.
 */
class Getter_Annotation extends Method_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           string
	 * @param $property        Reflection
	 * @param $annotation_name string
	 */
	public function __construct($value, Reflection $property, $annotation_name = 'getter')
	{
		/** @var $property Reflection_Property */
		parent::__construct($value, $property, 'getter');
		if (empty($this->value)) {
			$link = ($property->getAnnotation('link')->value);
			if (!empty($link)) {
				$this->value = Getter::class . '::get' . $link;
			}
		}
	}

}
