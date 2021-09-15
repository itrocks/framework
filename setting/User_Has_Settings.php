<?php
namespace ITRocks\Framework\Setting;

use ITRocks\Framework\Setting\User\Has;

/**
 * Compatibility trait, to avoid crash of the software during this moved trait update
 *
 * @deprecated
 * @todo When all instances is updated, remove this trait
 */
trait User_Has_Settings
{
	use Has;

}
