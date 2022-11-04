<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Traits\Has_Name;

/**
 * A shop class
 *
 * @store_name test_shops
 */
class Shop
{
	use Has_Name;

	//----------------------------------------------------------------------------------- $categories
	/**
	 * @link Map
	 * @var Category[]
	 */
	public array $categories;

}
