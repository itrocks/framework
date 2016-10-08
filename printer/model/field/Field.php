<?php
namespace SAF\Framework\Printer\Model;

use SAF\Framework\Mapper\Component;
use SAF\Framework\Traits\Has_Name;

/**
 * A print model field is a little zone into the page that contains the description of the data
 * to be printed (eg. link to a property, constant text, a drawing)
 *
 * @business
 * @set Printer_Model_Fields
 */
class Field
{
	use Component;
	use Has_Name;

	//----------------------------------------------------------------------------------------- $page
	/**
	 * The link to the page which contains the zone
	 *
	 * @composite
	 * @link Object
	 * @var Page
	 */
	public $page;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->page) . SP . strval($this->name);
	}

}
