<?php
namespace ITRocks\Framework\Address;

/**
 * Physical person trait : use it for classes that represent a physical person.
 *
 * @before_write generateName
 * @business
 * @representative first_name, last_name
 * @sort first_name, last_name
 */
trait Person
{

	//------------------------------------------------------------------------------------- $civility
	/**
	 * @link Object
	 * @var Civility
	 */
	public $civility;

	//----------------------------------------------------------------------------------- $first_name
	/**
	 * @var string
	 */
	public $first_name;

	//------------------------------------------------------------------------------------ $last_name
	/**
	 * @var string
	 */
	public $last_name;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		$result = trim($this->first_name . SP . $this->last_name);
		if (empty($result) && method_exists(get_parent_class($this), '__toString')) {
			/** @noinspection PhpUndefinedMethodInspection method exists */
			$result = parent::__toString();
		}
		return $result;
	}

	//------------------------------------------------------------------------------ setAutomaticName
	public function generateName(){
		if (empty($this->name)) {
			$this->name = $this->__toString();
		}
	}

}
