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
	public function __toString() : string
	{
		/** @var $self self|Has_Name */
		$self   = $this;
		$result = $self->parentToString();
		if (
			$result && $self->name
			&& (strpos($result, $self->name) === false)
			&& (strpos($self->name, $result) === false)
		) {
			$result = $self->name . SP . '(' . $result . ')';
		}
		return $result;
	}

	//------------------------------------------------------------------------- setDefaultNameIfEmpty
	/**
	 * Sets the name to "[$civility] $first_name $last_name" if empty
	 */
	public function setDefaultNameIfEmpty()
	{
		/** @var $self self|Has_Name */
		$self = $this;
		if (empty($self->name)) {
			$this->setDefaultName();
		}
	}

}
