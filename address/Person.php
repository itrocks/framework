<?php
namespace ITRocks\Framework\Address;

/**
 * Physical person trait : use it for classes that represent a physical person.
 */
trait Person
{

	//----------------------------------------------------------------------------------- $first_name
	public string $first_name = '';

	//------------------------------------------------------------------------------------ $last_name
	public string $last_name = '';

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		$result = trim($this->first_name . SP . $this->last_name);
		if (
			empty($result)
			&& get_parent_class($this)
			&& method_exists(get_parent_class($this), '__toString')
		) {
			/** @noinspection PhpUndefinedClassInspection all method_exists */
			$result = parent::__toString();
		}
		return $result;
	}

}
