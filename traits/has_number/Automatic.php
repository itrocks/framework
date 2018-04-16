<?php
namespace ITRocks\Framework\Traits\Has_Number;

use ITRocks\Framework\Objects\Counter;
use ITRocks\Framework\Traits\Has_Number;

/**
 * @before_write incrementNumber
 * @override number @calculated @mandatory false @user readonly
 */
trait Automatic
{
	use Has_Number;

	//------------------------------------------------------------------------------- incrementNumber
	/**
	 * This calculates $number if it is empty, using the Counter which identifier is the class name
	 */
	public function incrementNumber()
	{
		if (empty($this->number)) {
			$this->number = Counter::increment($this);
		}
	}

}
