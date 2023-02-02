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
	public ?Category $main_super_category;

	//---------------------------------------------------------------------------------------- $shops
	/**
	 * @var Shop[]
	 */
	public array $shops;

	//------------------------------------------------------------------------------- $sub_categories
	/**
	 * @var Category[]
	 */
	public array $sub_categories;

	//----------------------------------------------------------------------------- $super_categories
	/**
	 * @var Category[]
	 */
	public array $super_categories;

}
