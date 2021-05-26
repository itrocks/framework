<?php
namespace ITRocks\Framework\User;

use ITRocks\Framework\User;

/**
 * @extends User
 * @feature An user can be active or not
 * @see User
 */
trait Has_Active
{

	//--------------------------------------------------------------------------------------- $active
	/**
	 * @var boolean
	 */
	public bool $active = true;

	//-------------------------------------------------------------------------------------- isActive
	/**
	 * @return boolean
	 */
	public function isActive() : bool
	{
		return $this->active;
	}

}
