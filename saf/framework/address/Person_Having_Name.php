<?php
namespace SAF\Framework\Address;

use SAF\Framework\Traits\Has_Name;

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
		$this->name = trim($this->first_name . SP . $this->last_name);
	}

}
