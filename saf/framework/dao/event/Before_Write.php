<?php
namespace SAF\Framework\Dao\Event;

use SAF\Framework\Dao\Option;

/**
 * Classes implementing this interface will execute beforeWrite() before the object is written by data link
 */
interface Before_Write
{

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * @param $options Option[]
	 * @return boolean if returns true, then the object can be written, else it won't !
	 */
	public function beforeWrite($options);

}
