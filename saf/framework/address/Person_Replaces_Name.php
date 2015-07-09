<?php
namespace SAF\Framework\Address;

use SAF\Framework\Traits\Has_Name;

/**
 * A Has_Name Person which $name is always replaced by "$first_name $last_name"
 *
 * @extends Has_Name
 * @override civility   @setter setName
 * @override first_name @setter setName
 * @override last_name  @setter setName
 */
trait Person_Replaces_Name
{
	use Person;

	/** @noinspection PhpUnusedPrivateMethodInspection @setter */
	//--------------------------------------------------------------------------------------- setName
	/**
	 * A generic setter for all properties that are a component for $this->name if self is a Has_Name
	 *
	 * @param $property_name string
	 * @param $value         string
	 */
	private function setName($property_name, $value)
	{
		$this->$property_name = $value;
		/** @var $this self|Has_Name */
		$this->name = trim(
			(isset($this->civility) ? $this->civility->code . SP : '')
			. $this->first_name . SP . $this->last_name
		);
	}

}
