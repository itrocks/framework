<?php
namespace ITRocks\Framework\Feature\List_Setting\Reset;

use ITRocks\Framework\Feature\List_Setting\Reset;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\User;

/**
 * User has a reset list setting property
 */
#[Extend(User::class)]
trait User_Has
{

	//---------------------------------------------------------------------------------- $reset_lists
	#[Values(Reset::class)]
	public string $reset_lists = '';

}
