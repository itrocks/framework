<?php
namespace ITRocks\Framework\Address;

use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Property\Setter;
use ITRocks\Framework\Reflection\Attribute\Property\User;

/**
 * A Has_Name Person which $name is always replaced by "$first_name $last_name"
 *
 * Compatible with Person or Person
 *
 * @override civility   @impacts name
 * @override first_name @impacts name
 * @override last_name  @impacts name
 */
#[Display_Order('first_name', 'last_name', 'name')]
#[Override('civility',   new Setter('setNameComponent'))]
#[Override('first_name', new Setter('setNameComponent'))]
#[Override('last_name',  new Setter('setNameComponent'))]
#[Override('name', new Setter('setNameComponent'), new User(User::HIDE_EDIT, User::HIDE_OUTPUT))]
trait Person_Replaces_Name
{
	use Person_Having_Name;

	//------------------------------------------------------------------------------ setNameComponent
	/**
	 * A generic setter for all properties that are a component for $this->name if self is a Has_Name
	 *
	 * @noinspection PhpUnused #Setter
	 * @param $property_name string @values first_name, last_name
	 */
	protected function setNameComponent(string $property_name, string $value) : void
	{
		$this->$property_name = $value;
		$this->setDefaultName();
	}

}
