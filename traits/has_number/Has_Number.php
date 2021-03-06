<?php
namespace ITRocks\Framework\Traits;

/**
 * For all classes having a number
 *
 * @representative number
 */
trait Has_Number
{

	//--------------------------------------------------------------------------------------- $number
	/**
	 * @mandatory
	 * @var string
	 */
	public $number;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->number);
	}

}
