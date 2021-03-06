<?php
namespace ITRocks\Framework\Tests\Objects;

/**
 * An item class
 *
 * @before_write beforeWrite
 * @property integer call_before_write
 * @store_name test_items
 */
class Item
{

	//----------------------------------------------------------------------------------------- $code
	/**
	 * @var string
	 */
	public $code;

	//-------------------------------------------------------------------------------- $cross_selling
	/**
	 * @link Map
	 * @var Item[]
	 */
	public $cross_selling;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * @link Map
	 * @var Order_Line[]
	 */
	public $lines;

	//-------------------------------------------------------------------------------- $main_category
	/**
	 * @link Object
	 * @var Category
	 */
	public $main_category;

	//---------------------------------------------------------------------------------------- $model
	/**
	 * @link Object
	 * @var Item
	 */
	public $model;

	//------------------------------------------------------------------------- $secondary_categories
	/**
	 * @link Map
	 * @var Category[]
	 */
	public $secondary_categories;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->code);
	}

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * Before write annotation code : increments a counter on a virtual property
	 */
	public function beforeWrite()
	{
		$this->call_before_write = isset($this->call_before_write) ? 1 : ($this->call_before_write + 1);
	}

}
