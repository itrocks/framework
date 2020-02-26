<?php
namespace ITRocks\Framework\Address;

use ITRocks\Framework\Traits\Has_Name;

/**
 * Base trait for traits on Person applied on classes having Has_Name
 *
 * @extends Has_Name
 * @representative name
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
		/** @var $self self|Has_Name */
		$self = $this;
		$name = trim($self->first_name . SP . $self->last_name);
		if (strlen($name)) {
			$self->name = $name;
		}
	}

}
