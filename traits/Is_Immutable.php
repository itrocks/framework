<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Data_Link\Identifier_Map;

/**
 * Is_Immutable : Allow to manage storage of class only if exactly same values don't exist in Table
 *
 * @before_write beforeWriteOfImmutable
 */
trait Is_Immutable
{

	//------------------------------------------------------------------------ beforeWriteOfImmutable
	/**
	 * Called before write, this ensures that the object will be immutable into the data link
	 *
	 * Ignores the object identifier, and identifies it only with its property values :
	 * - If an object with the same property values exist in data store, then it will be linked to it
	 * - If it is a new object, it will created
	 *
	 * @param $link Data_Link
	 */
	public function beforeWriteOfImmutable(Data_Link $link = null)
	{
		if (!$link instanceof Identifier_Map) return;
		if (!$link) {
			$link = Dao::current();
		}

		// TODO this "form cleanup" code must be generalized into a cleanup plugin
		foreach (get_object_vars($this) as $property_name => $value) {
			if (is_string($value)) {
				$this->$property_name = $this->trimAll($value);
			}
		}

		$link->disconnect($this);
		if ($existing = $link->searchOne($this)) {
			$link->replace($this, $existing, false);
		}
	}

	//--------------------------------------------------------------------------------------- trimAll
	/**
	 * Remove all spaces from a string
	 *
	 * @param $string string to clean
	 * @return string string cleaned
	 */
	function trimAll($string)
	{
		$string = trim($string);
		$string = preg_replace("#[ ]+#", " ", $string);
		return $string;
	}

}
