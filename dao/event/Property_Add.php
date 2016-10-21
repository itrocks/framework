<?php
namespace SAF\Framework\Dao\Event;

use SAF\Framework\Dao\Data_Link;
use SAF\Framework\Dao\Option;
use SAF\Framework\Reflection\Reflection_Property;

/**
 * Dao property add event is fired when an element is added to a collection or map
 */
class Property_Add extends Add
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	public $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $link      Data_Link
	 * @param $object    object
	 * @param $new_value object
	 * @param $options   Option[]
	 * @param $property  Reflection_Property
	 */
	public function __construct(
		Data_Link $link, $object, $new_value, array &$options, Reflection_Property $property
	) {
		parent::__construct($link, $object, $new_value, $options);
		$this->property = $property;
	}

}
