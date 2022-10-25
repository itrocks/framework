<?php
namespace ITRocks\Framework\Component\Tab;

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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpDocSignatureInspection $object
	 * @param $object        object
	 * @param $property_path string
	 * @return Reflection_Property_Value
	 */
	protected function getProperty(object|string $object, string $property_path)
		: Reflection_Property_Value
	{
		/** @noinspection PhpUnhandledExceptionInspection object and property must be valid */
		return new Reflection_Property_Value($object, $property_path, $object, false, true);
	}

}
