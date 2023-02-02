<?php
namespace ITRocks\Framework\User\Active;

use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\User;

#[Extend(User::class)]
trait Has_Active
{

	//--------------------------------------------------------------------------------------- $active
	/**
	 * @var boolean
	 */
	public bool $active = true;

}
