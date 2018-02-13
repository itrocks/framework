<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Tools\Names;

/**
 * Has a default __toString method, that returns the display of the current class name
 */
trait Has_To_String
{

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return Names::classToDisplay(static::class);
	}

}
