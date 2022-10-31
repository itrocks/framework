<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao\Option;

/**
 * Has in() : this enable to search an option into a list of options
 */
trait Has_In
{

	//-------------------------------------------------------------------------------------------- in
	/**
	 * @param $options Option[]
	 * @return ?static
	 */
	public static function in(array $options) : ?static
	{
		foreach ($options as $option) {
			if (is_a($option, static::class)) {
				return $option;
			}
		}
		return null;
	}

}
