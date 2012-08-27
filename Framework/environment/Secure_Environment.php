<?php

class Secure_Environment
{

	/**
	 * @var User
	 */
	private $user;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param Data_Link $data_link
	 * @param User      $user
	 */
	public function __construct($data_link, $user)
	{
		parent::__construct($data_link);
		$this->setUser($user);
	}

	//------------------------------------------------------------------------------------ getCurrent
	/**
	 * @return Secure_Environment
	 */
	public static function getCurrent()
	{
		return parent::getCurrent();
	}

	//--------------------------------------------------------------------------------------- getUser
	/**
	 * @return User
	 */
	public function getUser()
	{
		return Getter::getObject($this->user, "User");
	}

	//--------------------------------------------------------------------------------------- setUser
	/**
	 * @param  User $user
	 * @return Secure_Environment
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

}
