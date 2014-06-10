<?php
namespace SAF\Framework\Reflection\Annotation\Sets;

use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Features for replaces annotations
 */
class Replaces_Annotations extends Annotation
{

	//---------------------------------------------------------------------- removeReplacedProperties
	/**
	 * @param $properties Reflection_Property[] key must be the name of the property
	 * @return Reflection_Property[] all properties but those that are replaced
	 */
	public static function removeReplacedProperties($properties)
	{
		foreach ($properties as $property) {
			$replaced_property_name = $property->getAnnotation('replaces')->value;
			if (!empty($replaced_property_name)) {
				unset($properties[$replaced_property_name]);
			}
		}
		return $properties;
	}

}
