<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Constant_Or_Callable;

/**
 * A Tooltip is a text to help the user to know how to fill in a form field
 *
 * @example @tooltip my text that will be translated
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Tooltip
{
	use Common;
	use Has_Constant_Or_Callable;

}
