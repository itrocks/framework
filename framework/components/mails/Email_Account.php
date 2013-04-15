<?php
namespace SAF\Framework;

class Email_Account
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
	 * @var Email_Pop_Account[]
	 */
	public $pop_accounts;

	//-------------------------------------------------------------------------------- $smtp_accounts
	/**
	 * @link Map
	 * @var Email_Smtp_Account[]
	 */
	public $smtp_accounts;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return str_replace(array("<", ">"), "", $this->name) . " <" . $this->email . ">";
	}

}
