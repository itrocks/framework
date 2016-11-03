<?php
namespace ITRocks\Framework\Printer\Model\Field;

use ITRocks\Framework\Printer\Model\Field;

/**
 * Printer model property field
 *
 * @business
 * @set Printer_Model_Properties
 */
class Property extends Field
{

	//-------------------------------------------------------------------------------- $property_path
	/**
	 * The path of the property, starting from the printer model context class
	 * @var string
	 */
	public $property_path;

}
