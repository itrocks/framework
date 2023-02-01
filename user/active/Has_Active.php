<?php
namespace ITRocks\Framework\User\Active;

use ITRocks\Framework\Reflection\Attribute\Class_\Extends_;
use ITRocks\Framework\User;

#[Extends_(User::class)]
trait Has_Active
{

	//--------------------------------------------------------------------------------------- $active
	/**
	 * @var boolean
	 */
	public bool $active = true;

}
