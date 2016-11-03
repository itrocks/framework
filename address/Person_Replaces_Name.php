<?php
namespace ITRocks\Framework\Address;

/**
 * A Has_Name Person which $name is always replaced by "$first_name $last_name"
 *
 * @override civility   @setter setName
 * @override first_name @setter setName
 * @override last_name  @setter setName
 */
trait Person_Replaces_Name
{
	use Person_Having_Name;

	//--------------------------------------------------------------------------------------- setName
	/** @noinspection PhpUnusedPrivateMethodInspection @setter */
	/**
	 * A generic setter for all properties that are a component for $this->name if self is a Has_Name
	 *
	 * @param $property_name string
	 * @param $value         string
	 */
	private function setName($property_name, $value)
	{
		$this->$property_name = $value;
		$this->setDefaultName();
	}

}
