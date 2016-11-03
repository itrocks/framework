<?php
namespace ITRocks\Framework\Address;

use ITRocks\Framework\Traits\Has_Name;

/**
 * A Has_Name Person which $name is set to "$first_name $last_name" when empty
 *
 * @before_write setDefaultNameIfEmpty
 * @override name @mandatory false
 */
trait Person_Set_Default_Name
{
	use Person_Having_Name { __toString as private parentToString; }

	//------------------------------------------------------------------------------------ __toString
	/**
	 * Returns name and first name and last name if the name is different
	 *
	 * @returns string
	 */
	public function __toString()
	{
		/** @var $this self|Has_Name */
		$result = $this->parentToString();
		if (
			$result && $this->name
			&& (strpos($result, $this->name) === false)
			&& (strpos($this->name, $result) === false)
		) {
			$result = $this->name . SP . '(' . $result . ')';
		}
		return $result;
	}

	//------------------------------------------------------------------------- setDefaultNameIfEmpty
	/**
	 * Sets the name to "$civility $first_name $last_name" if empty
	 */
	public function setDefaultNameIfEmpty()
	{
		if (empty($this->name)) {
			$this->setDefaultName();
		}
	}

}
