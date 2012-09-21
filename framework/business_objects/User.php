<?php
namespace SAF\Framework;

class User
{
	use Current;

	//----------------------------------------------------------------------------------------- $acls
	/**
	 * @foreign user
	 * @var multitype:Acl_Group
	 */
	public $acls;

	//---------------------------------------------------------------------------------------- $login
	/**
	 * @var string
	 */
	public $login;

	//------------------------------------------------------------------------------------- $password
	/**
	 * @var string
	 */
	public $password;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Build a User object, optionnaly with it's login and password initialization
	 *
	 * @param string $login
	 * @param string $password
	 */
	public function __construct($login = "", $password = "")
	{
		$this->login    = $login;
		$this->password = $password;
	}

}
