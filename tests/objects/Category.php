<?php
namespace SAF\Tests\Objects;

/**
 * A category class
 */
class Category
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//-------------------------------------------------------------------------- $main_super_category
	/**
	 * @link Object
	 * @var Category
	 */
	public $main_super_category;

	//----------------------------------------------------------------------------- $super_categories
	/**
	 * @link Map
	 * @var Category[]
	 */
	public $super_categories;

	//------------------------------------------------------------------------------- $sub_categories
	/**
	 * @link Map
	 * @var Category[]
	 */
	public $sub_categories;

	//---------------------------------------------------------------------------------------- $shops
	/**
	 * @link Map
	 * @var Shop[]
	 */
	public $shops;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

}
