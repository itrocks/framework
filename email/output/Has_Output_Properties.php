<?php
namespace ITRocks\Framework\Email\Output;

use ITRocks\Framework\Email;
use ITRocks\Framework\Property\Reflection_Property;
use ITRocks\Framework\Reflection\Attribute\Class_\Extends_;
use ITRocks\Framework\Reflection\Reflection_Property_Value;

#[Extends_(Email::class)]
trait Has_Output_Properties
{

	//------------------------------------------------------------------------------ outputProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnused object.html
	 * @return Reflection_Property[]
	 */
	public function outputProperties() : array
	{
		$properties     = [];
		$property_names = [
			'from', 'to', 'copy_to', 'blind_copy_to', 'send_message', 'subject', 'content', 'attachments'
		];
		foreach ($property_names as $property_name) {
			/** @noinspection PhpUnhandledExceptionInspection properties are valid */
			$properties[$property_name] = new Reflection_Property_Value($this, $property_name);
		}
		return $properties;
	}

}
