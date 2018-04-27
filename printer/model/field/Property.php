<?php
namespace ITRocks\Framework\Printer\Model\Field;

use ITRocks\Framework\Printer\Model\Field;

/**
 * Printer model property field
 *
 * @business
 * @store_name printer_model_properties
 */
class Property extends Field
{

	//-------------------------------------------------------------------------------- $property_path
	/**
	 * The path of the property, starting from the printer model context class
	 *
	 * @var string
	 */
	public $property_path;

}
