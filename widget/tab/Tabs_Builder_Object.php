<?php
namespace ITRocks\Framework\Widget\Tab;

use Exception;
use ITRocks\Framework\Reflection\Reflection_Property_Value;

/**
 * Tabs builder : build tabs for an object
 *
 * This fills in properties 'display' and 'value' special properties, useful ie for
 * Html_Template_Functions (in addition to Tabs_Builder_Class
 */
class Tabs_Builder_Object extends Tabs_Builder_Class
{

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * @param $object        object
	 * @param $property_path string
	 * @return Reflection_Property_Value
	 * @throws Exception
	 */
	protected function getProperty($object, $property_path)
	{
		if (!is_object($object)) {
			throw new Exception('$object parameter must be an object');
		}
		return new Reflection_Property_Value(get_class($object), $property_path, $object, false, true);
	}

}
