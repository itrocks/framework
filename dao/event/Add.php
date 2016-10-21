<?php
namespace SAF\Framework\Dao\Event;

use SAF\Framework\Dao\Data_Link;
use SAF\Framework\Dao\Event;
use SAF\Framework\Dao\Option;

/**
 * Dao Add event
 */
abstract class Add extends Event
{

	//------------------------------------------------------------------------------------ $new_value
	/**
	 * @var object
	 */
	public $new_value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $link      Data_Link
	 * @param $object    object
	 * @param $new_value object
	 * @param $options   Option[]
	 */
	public function __construct($link, $object, $new_value, &$options)
	{
		parent::__construct($link, $object, $options);
		$this->new_value = $new_value;
	}

}
