<?php
namespace ITRocks\Framework\User\Authenticate;

use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\User;

/**
 * Allow authenticate using a short-life token given by a previous process
 */
class By_Token implements Registerable
{

	//----------------------------------------------------------------------------------------- TOKEN
	const TOKEN = 'TOKEN';

	//----------------------------------------------------------------------------------------- apply
	/**
	 * Apply a token sent to the main controller to authenticate the matching user
	 *
	 * @param $get  array
	 * @param $post array
	 */
	public function apply(&$get, &$post)
	{
		if (isset($get[static::TOKEN])) {
			$token = $get[static::TOKEN];
			unset($get[static::TOKEN]);
		}
		if (isset($post[static::TOKEN])) {
			$token = $post[static::TOKEN];
			unset($post[static::TOKEN]);
		}
		if (!isset($token)) {
			return;
		}
		$creation = Date_Time::now()->sub(1, Date_Time::MINUTE);
		$token    = Dao::searchOne(
			['code' => $token, 'creation' => Func::greater($creation)],
			Token::class
		);
		if (!$token) {
			return;
		}
		Authentication::authenticate($token->user);
		Dao::delete($token);
		$this->purge();
	}

	//-------------------------------------------------------------------------------------- newToken
	/**
	 * @param $user   User
	 * @param $prefix string
	 * @return string
	 */
	public function newToken(User $user = null, $prefix = '')
	{
		if (!$user) {
			$user = User::current();
		}
		$token       = new Token();
		$token->code = uniqid($prefix, true);
		$token->user = $user;
		Dao::write($token);
		return $token->code;
	}

	//----------------------------------------------------------------------------------------- purge
	/**
	 * purge all old tokens
	 */
	public function purge()
	{
		$creation = Date_Time::now()->sub(1, Date_Time::MINUTE);
		foreach (Dao::search(['creation' => Func::lessOrEqual($creation)], Token::class) as $token) {
			Dao::delete($token);
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->afterMethod([Main::class, 'createSession'], [$this, 'apply']);
	}

}
