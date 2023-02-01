<?php
namespace ITRocks\Framework\Address;

use ITRocks\Framework\Reflection\Attribute\Class_\Extends_;
use ITRocks\Framework\Traits\Has_Name;

/**
 * Base trait for traits on Person applied on classes having Has_Name
 */
#[Extends_(Has_Name::class)]
trait Person_Having_Name
{
	use Person;

	//-------------------------------------------------------------------------------- setDefaultName
	/**
	 * Sets default name to first name + last name
	 */
	public function setDefaultName() : void
	{
		/** @var $this static|Has_Name */
		$name = trim($this->first_name . SP . $this->last_name);
		if ($name !== '') {
			$this->name = $name;
		}
	}

}
