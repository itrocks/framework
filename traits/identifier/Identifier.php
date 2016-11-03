<?php
namespace ITRocks\Framework\Traits;

/**
 * An unique identifier
 */
class Identifier
{
	use Has_Name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name string
	 */
	public function __construct($name = null)
	{
		if (isset($name)) {
			$this->name = $name;
		}
	}

}
