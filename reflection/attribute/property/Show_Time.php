<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;

/**
 * Tells that for a Date_Time how we must show time to the user.
 * default / false is the same as 'auto' : time will be shown if not 00:00:00.
 * Others values are 'always' and 'never', 'auto' can also be set.
 */
#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Show_Time
{
	use Common;

	//---------------------------------------------------------------------------------------- ALWAYS
	/** Always display time, always with seconds too */
	const ALWAYS = '¤always¤';

	//------------------------------------------------------------------------------------------ AUTO
	/**
	 * Default and backward compatibility. Time is added if not 00:00:00, using #Show_Seconds.
	 *
	 * @see Date_Format::$show_seconds
	 */
	const AUTO = '¤auto¤';

	//----------------------------------------------------------------------------------------- NEVER
	/** Never display time */
	const NEVER = '¤never¤';

	//---------------------------------------------------------------------------------------- $value
	public bool|string $value;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(bool|string $show_time = self::AUTO)
	{
		$this->value = $show_time;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return match($this->value) {
			true    => '1',
			false   => '0',
			default => $this->value
		};
	}

}
