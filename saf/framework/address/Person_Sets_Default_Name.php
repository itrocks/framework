<?php
namespace SAF\Framework\Address;

use SAF\Framework\Traits\Has_Name;

/**
 * A Has_Name Person which $name is set to "$first_name $last_name" when empty
 */
trait Person_Sets_Default_Name
{
	use Person_Replaces_Name {
		__toString as private parentToString;
		setName    as private replaceName;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * Returns name and first name and last name if the name is different (ie organisation name)
	 *
	 * @returns string
	 */
	public function __toString()
	{
		/** @var $this self|Has_Name */
		$result = $this->parentToString();
		if ((strpos($result, $this->name) === false) && (strpos($this->name, $result) === false)) {
			$result = $this->name . SP . '(' . $result . ')';
		}
		return $result;
	}

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
		if (empty($this->name)) {
			$this->replaceName($property_name, $value);
		}
	}

}
