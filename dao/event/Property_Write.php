<?php
namespace SAF\Framework\Dao\Event;

use SAF\Framework\Dao\Data_Link;
use SAF\Framework\Dao\Option;
use SAF\Framework\Reflection\Reflection_Property;

/**
 * Dao property write event for collections and maps
 */
class Property_Write extends Write
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	public $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $link      Data_Link
	 * @param $new_value object
	 * @param $old_value object
	 * @param $options   Option[]
	 * @param $property  Reflection_Property
	 */
	public function __construct(
		Data_Link $link, $new_value, $old_value, array &$options, Reflection_Property $property
	) {
		parent::__construct($link, $new_value, $old_value, $options);
		$this->property = $property;
	}

}
