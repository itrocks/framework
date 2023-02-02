<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Property\Alias;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A brand
 *
 * @feature
 */
#[Override('name', new Alias('brand'))]
class Brand
{
	use Has_Name;

}
