<?php
namespace ITRocks\Framework\Layout\Print_Model\Filtered_By_Features;

use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\RAD\Feature;

/**
 * Allow to configure print model filtered by features, but do not filter them
 *
 * @extends Print_Model
 * @see Print_Model
 */
trait Has_Configurable_Features
{

	//------------------------------------------------------------------------------------- $features
	/**
	 * @link Map
	 * @var Feature[]
	 */
	public $features;

}
