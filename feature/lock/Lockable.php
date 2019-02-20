<?php
namespace ITRocks\Framework\Feature\Lock;

use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Data_Link\Identifier_Map;

/**
 * Apply this to any object that can be locked using a lock button
 *
 * The object can't be modified (or deleted) if it is locked
 *
 * @before_delete isModifiable
 * @before_write  isModifiable
 */
trait Lockable
{

	//--------------------------------------------------------------------------------------- $locked
	/**
	 * @user hidden
	 * @var boolean
	 */
	public $locked;

	//---------------------------------------------------------------------------------- isModifiable
	/**
	 * Determines if data were locked.
	 *
	 * @param $link Data_Link
	 * @return boolean
	 */
	public function isModifiable(Data_Link $link)
	{
		if ($link instanceof Identifier_Map) {
			if (
				$link->getObjectIdentifier($this)
				&& $link->searchOne([$this], get_class($this))->locked
			) {
				return false;
			}
		}
		return true;
	}

}
