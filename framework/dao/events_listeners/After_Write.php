<?php
namespace SAF\Framework;

/**
 * Classes implementing this interface will execute afterWrite() after the object is written by data link
 */
interface After_Write
{

	//------------------------------------------------------------------------------------ afterWrite
	/**
	 * @param $options Dao_Option[]
	 * @return boolean if returns true, then the object can be written, else it won't !
	 */
	public function afterWrite($options);

}
