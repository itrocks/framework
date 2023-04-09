<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Boolean_Value;

/**
 * Tells that for a Date_Time we must show seconds to the user.
 * If not (default), seconds are always hidden by Loc::dateToLocale()
 */
#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Show_Seconds
{
	use Common;
	use Has_Boolean_Value;

}
