<?php
namespace ITRocks\Framework\Tests\Objects;

use AllowDynamicProperties;
use ITRocks\Framework\Reflection\Attribute\Class_\Store_Name;
use ITRocks\Framework\Traits\Has_Code;

/**
 * An item class
 *
 * @before_write beforeWrite
 * @property integer call_before_write
 */
#[AllowDynamicProperties]
#[Store_Name('test_items')]
class Item
{
	use Has_Code;

	//-------------------------------------------------------------------------------- $cross_selling
	/**
	 * @link Map
	 * @var Item[]
	 */
	public array $cross_selling;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * @link Map
	 * @var Order_Line[]
	 */
	public array $lines;

	//-------------------------------------------------------------------------------- $main_category
	/**
	 * @link Object
	 * @var ?Category
	 */
	public ?Category $main_category;

	//---------------------------------------------------------------------------------------- $model
	/**
	 * @link Object
	 * @var ?Item
	 */
	public ?Item $model;

	//------------------------------------------------------------------------- $secondary_categories
	/**
	 * @link Map
	 * @var Category[]
	 */
	public array $secondary_categories;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->code;
	}

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * Before write annotation code : increments a counter on a virtual property
	 */
	public function beforeWrite() : void
	{
		$this->call_before_write = isset($this->call_before_write) ? 1 : ($this->call_before_write + 1);
	}

}
