<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A category class
 */
#[Store('test_categories')]
class Category
{
	use Has_Name;

	//-------------------------------------------------------------------------- $main_super_category
	/**
	 * @link Object
	 * @var ?Category
	 */
	public ?Category $main_super_category;

	//---------------------------------------------------------------------------------------- $shops
	/**
	 * @link Map
	 * @var Shop[]
	 */
	public array $shops;

	//------------------------------------------------------------------------------- $sub_categories
	/**
	 * @link Map
	 * @var Category[]
	 */
	public array $sub_categories;

	//----------------------------------------------------------------------------- $super_categories
	/**
	 * @link Map
	 * @var Category[]
	 */
	public array $super_categories;

}
