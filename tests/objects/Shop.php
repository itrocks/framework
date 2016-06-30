<?php
namespace SAF\Framework\Tests\Objects;

/**
 * A shop class
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
	public function __toString()
	{
		return strval($this->name);
	}

}
