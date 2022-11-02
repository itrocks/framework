<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao\Option;

/**
 * Dao count option ask for elements counting in queries
 * The $count property will be filled when the query is executed
 */
class Count implements Option
{
	use Has_In;

	//---------------------------------------------------------------------------------------- $count
	/**
	 * @var integer
	 */
	public int $count = 0;

}
