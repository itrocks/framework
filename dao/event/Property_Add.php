<?php
namespace ITRocks\Framework\Dao\Event;

use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * Dao property add event is fired when an element is added to a collection or map
 */
class Property_Add extends Add
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
	 * @param $new_value object
	 * @param $options   Option[]
	 * @param $property  Reflection_Property
	 */
	public function __construct(
		Data_Link $link, object $object, object $new_value, array &$options,
		Reflection_Property $property
	) {
		parent::__construct($link, $object, $new_value, $options);
		$this->property = $property;
	}

}
