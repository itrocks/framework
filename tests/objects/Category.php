<?php
namespace SAF\Framework\Tests\Objects;

/**
 * A category class
 */
class Category
{

	//-------------------------------------------------------------------------- $main_super_category
	/**
	 * @link Object
	 * @var Category
	 */
	public $main_super_category;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//---------------------------------------------------------------------------------------- $shops
	/**
	 * @link Map
	 * @var Shop[]
	 */
	public $shops;

	//------------------------------------------------------------------------------- $sub_categories
	/**
	 * @link Map
	 * @var Category[]
	 */
	public $sub_categories;

	//----------------------------------------------------------------------------- $super_categories
	/**
	 * @link Map
	 * @var Category[]
	 */
	public $super_categories;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

}
