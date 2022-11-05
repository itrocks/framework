<?php
namespace ITRocks\Framework\User\Password;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Email;
use ITRocks\Framework\Email\Recipient;
use ITRocks\Framework\Email\Sender\File;
use ITRocks\Framework\Email\Sender\Smtp;
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

	//---------------------------------------------------------------------------------- prepareEmail
	/**
	 * Send the token identifier to the user
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $user       User
	 * @param $identifier string
	 * @return Email
	 */
	protected function prepareEmail(User $user, string $identifier) : Email
	{
		/** @noinspection PhpUnhandledExceptionInspection class */
		$email     = Builder::create(Email::class);
		$name      = 'No-reply';
		$no_reply  = 'noreply@' . Session::current()->domainName();
		$template  = new Template($this, __DIR__ . '/email.html');
		$template->setParameters(['identifier' => $identifier]);
		$email->content = $template->parse();
		$email->from    = Dao::searchOne(['name' => $name, 'email' => $no_reply], Recipient::class)
			?: new Recipient($no_reply, $name);
		$email->subject = Loc::tr('Password reset');
		$email->to      = [new Recipient($user->email)];
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
		$this->sendEmail($this->prepareEmail($user, $token->identifier));
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
