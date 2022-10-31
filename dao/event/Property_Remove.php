<?php
namespace ITRocks\Framework\Dao\Event;

use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * Dao property remove event is fired when an element is removed from a collection or map
 */
class Property_Remove extends Delete
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	public Reflection_Property $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $link      Data_Link
	 * @param $object    object
	 * @param $old_value object
	 * @param $options   Option[]
	 * @param $property  Reflection_Property
	 */
	public function __construct(
		Data_Link $link, object $object, object $old_value, array &$options,
		Reflection_Property $property
	) {
		parent::__construct($link, $object, $old_value, $options);
		$this->property = $property;
	}

}
