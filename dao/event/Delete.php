<?php
namespace ITRocks\Framework\Dao\Event;

use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Event;
use ITRocks\Framework\Dao\Option;

/**
 * Dao delete event
 */
abstract class Delete extends Event
{

	//------------------------------------------------------------------------------------ $old_value
	/**
	 * @var object
	 */
	public object $old_value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $link      Data_Link
	 * @param $object    object
	 * @param $old_value object
	 * @param $options   Option[]
	 */
	public function __construct(Data_Link $link, object $object, object $old_value, array &$options)
	{
		parent::__construct($link, $object, $options);
		$this->old_value = $old_value;
	}

}
