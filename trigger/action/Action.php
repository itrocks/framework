<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Traits\Is_Immutable;
use ITRocks\Framework\User;

/**
 * Triggered action
 *
 * @representative action
 */
class Action
{
	use Is_Immutable;

	//--------------------------------------------------------------------------------------- $action
	/**
	 * @var string
	 */
	public $action;

	//-------------------------------------------------------------------------------------- $as_user
	/**
	 * @link Object
	 * @var User
	 */
	public $as_user;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->action);
	}

}
