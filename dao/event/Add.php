<?php
namespace ITRocks\Framework\Dao\Event;

use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Event;
use ITRocks\Framework\Dao\Option;

/**
 * Dao Add event
 */
abstract class Add extends Event
{

	//------------------------------------------------------------------------------------ $new_value
	/**
	 * @var object
	 */
	public object $new_value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $link      Data_Link
	 * @param $object    object
	 * @param $new_value object
	 * @param $options   Option[]
	 */
	public function __construct(Data_Link $link, object $object, object $new_value, array &$options)
	{
		parent::__construct($link, $object, $options);
		$this->new_value = $new_value;
	}

}
