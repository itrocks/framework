<?php
namespace ITRocks\Framework\Email;

use ITRocks\Framework\Traits\Has_Email;
use ITRocks\Framework\Traits\Has_Name;

/**
 * An email account : configuration of multi-protocols access to a given email box
 *
 * @business
 * @store_name email_accounts
 */
class Account
{
	use Has_Email;
	use Has_Name;

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
