<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * A property context annotation needs the property to be properly built
 * Annotations class that are intended for properties only should implement this
 *
 * @see Class_Context_Annotation
 * @see Reflection_Context_Annotation
 */
interface Property_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    ?string
	 * @param $property Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct(?string $value, Reflection_Property $property);

}
