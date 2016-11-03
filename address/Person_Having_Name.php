<?php
namespace ITRocks\Framework\Address;

use ITRocks\Framework\Traits\Has_Name;

/**
 * Base trait for traits on Person applied on classes having Has_Name
 *
 * @extends Has_Name
 */
trait Person_Having_Name
{
	use Person;

	//-------------------------------------------------------------------------------- setDefaultName
	/**
	 * Sets default name to first name + last name
	 */
	public function setDefaultName()
	{
		/** @var $this self|Has_Name */
		$name = trim($this->first_name . SP . $this->last_name);
		if (strlen($name)) {
			$this->name = trim($this->first_name . SP . $this->last_name);
		}
	}

}
