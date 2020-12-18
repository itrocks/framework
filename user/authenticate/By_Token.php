<?php
namespace ITRocks\Framework\User\Authenticate;

use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\User;

/**
 * Allow authenticate using a short-life token given by a previous process
 */
class By_Token implements Registerable
{

	//------------------------------------------------------------------------------------------- SID
	const SID = 'getSID';

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
		if ($get[static::TOKEN] ?? false) {
			$token = $get[static::TOKEN];
			unset($get[static::TOKEN]);
		}
		if ($post[static::TOKEN] ?? false) {
			$token = $post[static::TOKEN];
			unset($post[static::TOKEN]);
		}
		if (!isset($token)) {
			return;
		}
		$this->purge();
		/** @var $token Token */
		$token = Dao::searchOne(['code' => $token], Token::class);
		if (!$token) {
			return;
		}
		Authentication::authenticate($token->user);
		if ($token->single_use) {
			Dao::delete($token);
		}
		if ($get[static::SID] ?? $post[static::SID] ?? false) {
			echo '[' . Session::sid() . ']';
		}
	}

	//-------------------------------------------------------------------------------------- newToken
	/**
	 * @param $user   User|null
	 * @param $prefix string
	 * @return Token
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
		return $token;
	}

	//----------------------------------------------------------------------------------------- purge
	/**
	 * purge all old tokens
	 */
	public function purge()
	{
		$search = ['validity_end_date' => Func::lessOrEqual(Date_Time::now())];
		foreach (Dao::search($search, Token::class) as $token) {
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
