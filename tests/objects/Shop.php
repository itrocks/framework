<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A shop class
 */
#[Store('test_shops')]
class Shop
{
	use Has_Name;

	//----------------------------------------------------------------------------------- $categories
	/**
	 * @var Category[]
	 */
	public array $categories;

}
