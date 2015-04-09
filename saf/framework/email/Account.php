<?php
namespace SAF\Framework\Email;

use SAF\Framework\Traits\Has_Name;

/**
 * An email account : configuration of multi-protocols access to a given email box
 */
class Account
{
	use Has_Name;

	//---------------------------------------------------------------------------------------- $email
	/**
	 * @var string
	 */
	public $email;

	//--------------------------------------------------------------------------------- $pop_accounts
	/**
	 * @link Map
	 * @var Pop_Account[]
	 */
	public $pop_accounts;

	//-------------------------------------------------------------------------------- $smtp_accounts
	/**
	 * @link Map
	 * @var Smtp_Account[]
	 */
	public $smtp_accounts;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return str_replace(['<', '>'], '', $this->name) . ' <' . $this->email . '>';
	}

}
