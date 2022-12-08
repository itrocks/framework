<?php
namespace ITRocks\Framework\User\Password;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Email;
use ITRocks\Framework\Email\Recipient;
use ITRocks\Framework\Email\Sender\File;
use ITRocks\Framework\Email\Sender\Smtp;
use ITRocks\Framework\Locale\Has_Language;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Password;
use ITRocks\Framework\User\Password\Reset\Token;
use ITRocks\Framework\View\Html\Template;

/**
 * @extends Password
 */
trait Reset
{

	//----------------------------------------------------------------------------------------- apply
	/**
	 * @param $token string
	 * @return boolean
	 */
	public function apply(string $token) : bool
	{
		$applied = 0;
		Dao::begin();
		$search = ['done' => Func::equal(Date_Time::min()), 'identifier' => $token];
		foreach (Dao::search($search, Token::class) as $token) {
			$token->user->password = $token->new_password;
			Dao::write($token->user, Dao::only('password'));
			$token->done = Date_Time::now();
			Dao::write($token, Dao::only('done'));
			$applied ++;
		}
		Dao::commit();
		return $applied;
	}

	//---------------------------------------------------------------------------------- informNoUser
	/**
	 * Sends an email to the requester to inform him he as no account associated to this email address
	 */
	public function informNoUser() : void
	{
		$this->sendEmail($this->prepareEmail(null, ['email' => $this->login], 'no-account-email'));
	}

	//---------------------------------------------------------------------------------- prepareEmail
	/**
	 * Send the token identifier to the user
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $user       User|Has_Language|null
	 * @param $parameters string[]
	 * @param $template   string
	 * @return Email
	 */
	protected function prepareEmail(
		User|Has_Language|null $user, array $parameters, string $template = 'email'
	) : Email
	{
		/** @noinspection PhpUnhandledExceptionInspection class */
		$email    = Builder::create(Email::class);
		$name     = 'No-reply';
		$no_reply = 'noreply@' . Session::current()->domainName();
		$language = isA($user, Has_Language::class)
			? strtolower($user->language->code)
			: (substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en', 0, 2) ?: 'en');
		$path     = "user/password/reset/$template-";
		$path     = stream_resolve_include_path($path . $language . '.html')
			?: stream_resolve_include_path($path . 'en.html');
		$template = new Template($this, $path);
		$template->setParameters($parameters);
		$email->content = $template->parse();
		$email->from    = Dao::searchOne(['name' => $name, 'email' => $no_reply], Recipient::class)
			?: new Recipient($no_reply, $name);
		$email->subject = Loc::tr('Password reset', $user ?: []);
		$email->to      = [new Recipient($user ? $user->email : $this->login)];
		Dao::write($email);
		return $email;
	}

	//----------------------------------------------------------------------------------------- reset
	/**
	 * Use the password data to reset password
	 */
	public function reset() : void
	{
		if (!($this->login && $this->password && $this->password2)) {
			return;
		}
		/** @var $users User[] */
		$users =
			(str_contains($this->login, AT) ? Dao::search(['email' => $this->login], User::class) : null)
			?: Dao::search(['login' => $this->login], User::class);
		if (!$users) {
			if (preg_match('/.+@.+\...+/', $this->login)) {
				$this->informNoUser();
			}
			return;
		}
		if ($this->password !== $this->password2) {
			return;
		}
		foreach ($users as $user) {
			$this->resetUser($user);
		}
	}

	//------------------------------------------------------------------------------------- resetUser
	/**
	 * @param $user User
	 */
	public function resetUser(User $user) : void
	{
		if (!$user->email) {
			return;
		}
		$token = new Token();
		$token->identifier   = str_replace(DOT, '', uniqid('', true));
		$token->new_password = $this->password;
		$token->user         = $user;
		Dao::write($token);
		$this->sendEmail($this->prepareEmail($user, ['identifier' => $token->identifier]));
	}

	//------------------------------------------------------------------------------------- sendEmail
	/**
	 * @param $email Email
	 */
	protected function sendEmail(Email $email) : void
	{
		if ($sender = (Smtp::get(false) ?: File::get(false))) {
			$sender->send($email);
		}
	}

}
