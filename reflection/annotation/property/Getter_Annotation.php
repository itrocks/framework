<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Tells a method name that is the getter for that property.
 *
 * The getter will be called each time the program accesses the property.
 * When there is a @link annotation and no @getter, a defaut @getter is set with the Dao access
 * common method depending on the link type.
 */
class Getter_Annotation extends Method_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'getter';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           ?string
	 * @param $property        Reflection|Reflection_Property
	 * @param $annotation_name string
	 */
	public function __construct(
		string|null $value, Reflection|Reflection_Property $property,
		string $annotation_name = self::ANNOTATION
	) {
		parent::__construct($value, $property, self::ANNOTATION);
		if (empty($this->value)) {
			$link = Link_Annotation::of($property)->value;
			if ($link) {
				$this->value = Getter::class . '::get' . $link;
			}
		}
	}

}
