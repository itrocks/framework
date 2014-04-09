<?php
namespace SAF\Framework\Dao\Event;

use SAF\Framework\Dao\Option;

/**
 * Classes implementing this interface will execute afterWrite() after the object is written by data link
 */
interface After_Write
{

	//------------------------------------------------------------------------------------ afterWrite
	/**
	 * @param $options Option[]
	 * @return boolean if returns true, then the object can be written, else it won't !
	 */
	public function afterWrite($options);

}
