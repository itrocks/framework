<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Dao\Option\Has_In;

/**
 * Link property name Dao option
 */
class Link_Property_Name implements Option
{
	use Has_In;

	//--------------------------------------------------------------------------- $link_property_name
	/**
	 * @var string
	 */
	public $link_property_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $link_property_name string
	 */
	public function __construct($link_property_name = null)
	{
		if (isset($link_property_name)) {
			$this->link_property_name = $link_property_name;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->link_property_name;
	}

}
