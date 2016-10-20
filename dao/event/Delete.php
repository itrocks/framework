<?php
namespace SAF\Framework\Dao\Event;

use SAF\Framework\Dao\Data_Link;
use SAF\Framework\Dao\Event;
use SAF\Framework\Dao\Option;

/**
 * Dao property delete event for collections and maps
 */
abstract class Delete extends Event
{

	//------------------------------------------------------------------------------------ $old_value
	/**
	 * @var object
	 */
	public $old_value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $link      Data_Link
	 * @param $old_value object
	 * @param $options   Option[]
	 */
	public function __construct(Data_Link $link, $old_value, array &$options)
	{
		parent::__construct($link, $options);
		$this->old_value = $old_value;
	}

}
