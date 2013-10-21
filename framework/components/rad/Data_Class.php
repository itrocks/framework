<?php
namespace SAF\Framework\RAD;

use SAF\Framework\Component;

/**
 * A data class is used to store data, ie linked to a database
 *
 * It includes management rules for it's data
 */
class Data_Class
{
	use Component;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

}
