<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao\Data_Link;

/**
 * Is_Immutable : Allow to manage storage of class only if exactly same values don't exist in Table
 *
 * @before_write autoLink
 */
trait Is_Immutable
{

	//-------------------------------------------------------------------------------------- autoLink
	/**
	 * Called before write, this ensures that the object will be immutable into the data link
	 * - if the object already exists into data store, then an exception will occurred
	 * - if the object does not already exists, then save the object as a new one
	 *
	 * @param $link Data_Link
	 */
	public function autoLink(Data_Link $link = null)
	{
		$manager = Builder::create(Immutable_Manager::class, [$link, $this]);
		$manager->run();
	}

}
