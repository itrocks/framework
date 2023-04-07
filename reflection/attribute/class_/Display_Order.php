<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Always;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Is_List;

/**
 * This enables to define a global sort for properties display
 *
 * @example
 * #[Display_Order('property_1, property_2, property_3')]
 * #[Display_Order('property_1', 'property_2', 'property_3')]
 */
#[Always, Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS), Inheritable]
class Display_Order
{
	use Common;
	use Is_List;

}
