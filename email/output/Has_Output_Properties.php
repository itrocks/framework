<?php
namespace ITRocks\Framework\Email\Output;

use ITRocks\Framework\Email;
use ITRocks\Framework\Property\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;

/**
 * @extends Email
 * @see Email
 */
trait Has_Output_Properties
{

	//------------------------------------------------------------------------------ outputProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Property[]
	 */
	public function outputProperties()
	{
		$properties     = [];
		$property_names = ['to', 'copy_to', 'blind_copy_to', 'subject', 'content', 'attachments'];
		foreach ($property_names as $property_name) {
			/** @noinspection PhpUnhandledExceptionInspection properties are valid */
			$properties[$property_name] = new Reflection_Property_Value($this, $property_name);
		}
		return $properties;
	}

}
