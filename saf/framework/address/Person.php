<?php
namespace SAF\Framework\Address;

use SAF\Framework\Traits\Has_Name;

/**
 * Physical person trait : use it for classes that represent a physical person.
 *
 * @business
 * @representative first_name, last_name
 * @sort first_name, last_name
 */
trait Person
{

	//------------------------------------------------------------------------------------- $civility
	/**
	 * @link Object
	 * @setter setName
	 * @var Civility
	 */
	public $civility;

	//----------------------------------------------------------------------------------- $first_name
	/**
	 * @setter setName
	 * @var string
	 */
	public $first_name;

	//------------------------------------------------------------------------------------ $last_name
	/**
	 * @setter setName
	 * @var string
	 */
	public $last_name;

	//--------------------------------------------------------------------------------------- setName
	/* @noinspection PhpUnusedPrivateMethodInspection @setter */
	/**
	 * A generic setter for all properties that are a component for $this->name if self is a Has_Name
	 *
	 * @param $property_name string
	 * @param $value         string
	 */
	private function setName($property_name, $value)
	{
		$this->$property_name = $value;
		if (isA($this, Has_Name::class)) {
			/** @var $this self|Has_Name */
			$this->name = trim(
				(isset($this->civility) ? $this->civility->code . SP : '')
				. $this->first_name . SP . $this->last_name
			);
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		$result = trim($this->first_name . SP . $this->last_name);
		if (empty($result) && method_exists(get_parent_class($this), '__toString')) {
			/** @noinspection PhpUndefinedClassInspection method_exists */
			$result = parent::__toString();
		}
		return $result;
	}

}
