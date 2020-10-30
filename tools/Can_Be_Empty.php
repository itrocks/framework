<?php
namespace ITRocks\Framework\Tools;

/**
 * For all classes that embed an isEmpty() method
 */
interface Can_Be_Empty
{

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * @return boolean
	 */
	public function isEmpty() : bool;

}
