<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Traits\Has_Brand;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A model
 *
 * @display_order brand, name
 * @override name @alias model
 */
class Model
{
	use Has_Brand;
	use Has_Name;

}
