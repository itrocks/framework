<?php
namespace SAF\Framework\Tests\Objects;

/**
 * A shop class
 */
class Shop
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------- $categories
	/**
	 * @link Map
	 * @var Category[]
	 */
	public $categories;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

}
