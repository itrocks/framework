<?php
namespace ITRocks\Framework\Reflection\Annotation\Sets;

use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Features for replaces annotations
 */
abstract class Replaces_Annotations
{

	//---------------------------------------------------------------------- removeReplacedProperties
	/**
	 * @param $properties Reflection_Property[]|T[] key must be the name of the property
	 * @return T[] all properties but those that are replaced
	 * @template T
	 */
	public static function removeReplacedProperties(array $properties) : array
	{
		foreach ($properties as $property) {
			$replaced_property_name = $property->getAnnotation('replaces')->value;
			if (!empty($replaced_property_name)) {
				unset($properties[$replaced_property_name]);
			}
		}
		return $properties;
	}

	//----------------------------------------------------------------------------- replaceProperties
	/**
	 * When a property has been replaced, replace it into the properties list.
	 *
	 * All the properties of the list must have the same final class.
	 * Only properties that are not a.path are replaced
	 * TODO LOWEST make it work on all.path.properties (any property of the path may be replaced)
	 *
	 * @param $properties Reflection_Property[] key must be the name of the property
	 * @return Reflection_Property[] all replaced properties has been replaced
	 */
	public static function replaceProperties(array $properties = null) : array
	{
		if ($properties) {
			// replace properties with their replacement properties (key is still the old property name)
			$property = reset($properties);
			$replaced = false;
			foreach ($property->getFinalClass()->getProperties([T_EXTENDS, T_USE]) as $property) {
				if (!strpos($property, DOT)) {
					$replaced_property_name = $property->getAnnotation('replaces')->value;
					if (!empty($replaced_property_name) && isset($properties[$replaced_property_name])) {
						$properties[$replaced_property_name] = $property;
						$replaced = true;
					}
				}
			}
			// replace key : old property name becomes new property name for replaced properties
			if ($replaced) {
				$new_properties = [];
				foreach ($properties as $property_name => $property) {
					$property_name = strpos($property_name, DOT) ? $property_name : $property->getName();
					$new_properties[$property_name] = $property;
				}
				$properties = $new_properties;
			}
		}
		return $properties;
	}

}
