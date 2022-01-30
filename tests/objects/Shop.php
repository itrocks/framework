<?php
namespace ITRocks\Framework\Tests\Objects;

/**
 * A shop class
 *
 * @store_name test_shops
 */
class Shop
{

	//----------------------------------------------------------------------------------- $categories
	/**
	 * @link Map
	 * @var Category[]
	 */
	public $categories;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return strval($this->name);
	}

}
