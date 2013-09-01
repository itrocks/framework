<?php
namespace SAF\Tests;

/**
 * An item class
 */
class Item
{

	//----------------------------------------------------------------------------------------- $code
	/**
	 * @var string
	 */
	public $code;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * @link Map
	 * @var Order_Line[]
	 */
	public $lines;

	//---------------------------------------------------------------------------------------- $model
	/**
	 * @link Object
	 * @var Item
	 */
	public $model;

	//-------------------------------------------------------------------------------- $main_category
	/**
	 * @link Object
	 * @var Category
	 */
	public $main_category;

	//------------------------------------------------------------------------- $secondary_categories
	/**
	 * @link Map
	 * @var Category[]
	 */
	public $secondary_categories;

	//-------------------------------------------------------------------------------- $cross_selling
	/**
	 * @link Map
	 * @var Item[]
	 */
	public $cross_selling;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->code);
	}

}
