<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao\Option;

/**
 * Link property name Dao option
 */
class Link_Property_Name implements Option
{

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
	public function __toString()
	{
		return $this->link_property_name;
	}

	//-------------------------------------------------------------------------------------------- in
	/**
	 * Gets the Link_Property_Name option from $options (if there is one)
	 *
	 * @param $options Option[]
	 * @return static|null
	 */
	public static function in(array $options)
	{
		foreach ($options as $option) {
			if ($option instanceof static) {
				return $option;
			}
		}
		return null;
	}

}
