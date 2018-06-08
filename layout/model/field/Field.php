<?php
namespace ITRocks\Framework\Layout\Model;

use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A print model field is a little zone into the page that contains the description of the data
 * to be printed (eg. link to a property, constant text, a drawing)
 *
 * @business
 * @store_name layout_model_fields
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
