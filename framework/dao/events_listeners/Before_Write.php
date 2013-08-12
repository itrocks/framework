<?php
namespace SAF\Framework;

/**
 * Classes implementing this interface will execute beforeWrite() before the object is written by data link
 */
interface Before_Write
{

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * @param $options Dao_Option[]
	 * @return boolean if returns true, then the object can be written, else it won't !
	 */
	public function beforeWrite($options);

}
