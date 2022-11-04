<?php
namespace ITRocks\Framework\User\Authenticate;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\User;

/**
 * Allow to authenticate using a short-life token given by a previous process
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
	 * @param $get  string[]
	 * @param $post string[]
	 */
	public function apply(array &$get, array &$post)
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
		if ($post['checkToken'] ?? false) {
			$get['as_widget'] = true;
			echo 'OK:TOKEN:[' . $token->code . ']';
		}
	}

	//-------------------------------------------------------------------------------------- newToken
	/**
	 * @param $user      User|null
	 * @param $prefix    string
	 * @param $long_term boolean 1 minute single use or infinite duration multiple uses token
	 * @return Token
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function newToken(User $user = null, string $prefix = '', bool $long_term = false) : Token
	{
		if (!$user) {
			$user = User::current();
		}
		/** @noinspection PhpUnhandledExceptionInspection class */
		$token       = Builder::create(Token::class);
		$token->code = $prefix . sha1(sha1(uniqid('', true)));
		$token->user = $user;
		if ($long_term) {
			$token->single_use        = false;
			$token->validity_end_date = Date_Time::max();
		}
		return Dao::write($token);
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
	public function register(Register $register) : void
	{
		$register->aop->afterMethod([Main::class, 'createSession'], [$this, 'apply']);
	}

}
