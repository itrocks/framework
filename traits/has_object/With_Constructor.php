<?php
namespace ITRocks\Framework\Traits\Has_Object;

use ITRocks\Framework\Traits\Has_Object;

/**
 * For classes that have object with a constructor
 */
trait With_Constructor
{
	use Has_Object;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * A constructor for your Has_Object class
	 *
	 * @param $object object|null
	 */
	public function __construct(object $object = null)
	{
		if (isset($object)) {
			$this->object = $object;
		}
	}

}
