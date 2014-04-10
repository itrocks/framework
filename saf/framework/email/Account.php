<?php
namespace SAF\Framework\Email;

/**
 * An email account : configuration of multi-protocols access to a given email box
 */
class Account
{

	//---------------------------------------------------------------------------------------- $email
	/**
	 * @var string
	 */
	public $email;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

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
