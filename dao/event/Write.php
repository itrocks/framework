<?php
namespace SAF\Framework\Dao\Event;

use SAF\Framework\Dao\Data_Link;
use SAF\Framework\Dao\Event;
use SAF\Framework\Dao\Option;

/**
 * Dao write event
 */
abstract class Write extends Event
{

	//------------------------------------------------------------------------------------ $new_value
	/**
	 * @var object
	 */
	public $new_value;

	//------------------------------------------------------------------------------------ $old_value
	/**
	 * @var object
	 */
	public $old_value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $link      Data_Link
	 * @param $new_value object
	 * @param $old_value object
	 * @param $options   Option[]
	 */
	public function __construct($link, $new_value, $old_value, &$options)
	{
		parent::__construct($link, $options);
		$this->new_value = $new_value;
		$this->old_value = $old_value;
	}

}
