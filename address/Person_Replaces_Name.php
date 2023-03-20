<?php
namespace ITRocks\Framework\Address;

/**
 * A Has_Name Person which $name is always replaced by "$first_name $last_name"
 *
 * Compatible with Person or Person
 *
 * @display_order first_name, last_name, name
 * @override first_name @impacts name @setter setNameComponent
 * @override last_name  @impacts name @setter setNameComponent
 * @override name       @calculated @user hide_edit, hide_output
 */
trait Person_Replaces_Name
{
	use Person_Having_Name;

	//------------------------------------------------------------------------------ setNameComponent
	/**
	 * A generic setter for all properties that are a component for $this->name if self is a Has_Name
	 *
	 * @noinspection PhpUnused @setter
	 * @param $property_name string @values first_name, last_name
	 * @param $value         string
	 */
	protected function setNameComponent(string $property_name, string $value) : void
	{
		$this->$property_name = $value;
		$this->setDefaultName();
	}

}
