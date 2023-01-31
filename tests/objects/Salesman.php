<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A salesman class
 */
#[Store('test_salesmen')]
class Salesman
{
	use Has_Name\With_Constructor;

}
