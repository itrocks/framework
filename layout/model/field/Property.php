<?php
namespace ITRocks\Framework\Layout\Model\Field;

use ITRocks\Framework\Layout\Model\Field;

/**
 * Printer model property field
 *
 * @business
 * @store_name layout_model_properties
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
