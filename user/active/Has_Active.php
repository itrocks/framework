<?php
namespace ITRocks\Framework\User\Active;

use ITRocks\Framework\User;

/**
 * @extends User
 */
trait Has_Active
{

	//--------------------------------------------------------------------------------------- $active
	/**
	 * @var boolean
	 */
	public bool $active = true;

}
