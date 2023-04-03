<?php
namespace ITRocks\Framework\Address;

use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A Has_Name Person which $name is set to "$first_name $last_name" when empty
 *
 * @before_write setDefaultNameIfEmpty
 */
#[Override('name', new Mandatory(false))]
trait Person_Set_Default_Name
{
	use Person_Having_Name { __toString as private parentToString; }

	//------------------------------------------------------------------------------------ __toString
	/** Returns name and first name and last name if the name is different */
	public function __toString() : string
	{
		/** @var $self self|Has_Name */
		$self   = $this;
		$result = $self->parentToString();
		if (
			$result
			&& $self->name
			&& !str_contains($result, $self->name)
			&& !str_contains($self->name, $result)
		) {
			$result = $self->name . SP . '(' . $result . ')';
		}
		return $result;
	}

	//------------------------------------------------------------------------- setDefaultNameIfEmpty
	/**
	 * Sets the name to "[$civility] $first_name $last_name" if empty
	 *
	 * @noinspection PhpUnused @before_write
	 */
	public function setDefaultNameIfEmpty() : void
	{
		/** @var $self self|Has_Name */
		$self = $this;
		if (empty($self->name)) {
			$this->setDefaultName();
		}
	}

}
