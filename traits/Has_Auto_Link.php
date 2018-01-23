<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link;

/**
 * Has_AutoLink : Allow to manage storage of class only if exactly same values don't exist in Table
 *
 * @before_write autoLink
 */
trait Has_Auto_Link
{

	//-------------------------------------------------------------------------------------- autoLink
	/**
	 * Called before write, this ensures that the object will be immutable into the data link
	 * - if the object already exists into data store, then an exception will occured
	 * - if the object does not already exists, then save the object as a new one
	 *
	 * @param $link Data_Link
	 */
	public function autoLink(Data_Link $link = null)
	{
		// Remove spaces
		foreach (get_object_vars($this) as $key => $value) {
			if (is_string($value)) {
				$this->key = trim($value);
			}
		}
		if (!$link) {
			$link = Dao::current();
		}
		$link->disconnect($this);
		// No code in if
		$existing_object = $link->searchOne($this);
		if ($existing_object) {
			$link->replace($this, $existing_object, false);

			// FIXME Remove previous if not used

		}
	}

}
