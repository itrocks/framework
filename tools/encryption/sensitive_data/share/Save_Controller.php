<?php

namespace ITRocks\Framework\Tools\Encryption\Sensitive_Data\Share;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Email;
use ITRocks\Framework\Email\Recipient;
use ITRocks\Framework\Email\Sender;
use ITRocks\Framework\Feature\Message;
use ITRocks\Framework\Feature\Save\Controller;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Object_Builder_Array;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data\Cipher_Key;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data\Key;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data\Share;
use ITRocks\Framework\Tools\Password;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\Traits\Has_Name;
use ITRocks\Framework\User;

/** @noinspection PhpUnused Controller */
class Save_Controller extends Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Parameters $parameters, array $form, array $files, $class_name) : string
	{
		/** @noinspection PhpUnhandledExceptionInspection should be valid */
		/** @var $share Share|null */
		$share = (new Object_Builder_Array(Share::class))->build($form, new Share, true);

		if (!Sensitive_Data::isPasswordGlobalAndValid()) {
			$error = 'Bad cipher key';
		}
		elseif (!$share || !$share->user) {
			$error = 'User is mandatory';
		}
		elseif (Dao::count(['user' => $share->user], Key::class)) {
			$error = 'User already has access to sensitive data';
		}
		else {
			$user = User::current();
			// decrypt secret using current user cipher key
			/** @var $key Key */
			$key    = Dao::searchOne(['user' => User::current()], Key::class);
			$secret = $key->getSecret();
			// encrypt secret for the new user using a unique temporary token
			User::current($share->user);
			$_POST['sensitive_password'] = $token = (new Password)->generate(32, Password::T_ALL, '-');
			Sensitive_Data::password();
			$key              = new Key();
			$key->user        = $share->user;
			$key->valid_until = Date_Time::now()->add(1);
			/** @noinspection PhpUnhandledExceptionInspection valid call */
			$key->setSecret($secret);
			Dao::write($key);
			// back to the current user
			User::current($user);
			// send the temporary token to the user
			$this->sendEmail($share->user, $token, $key->valid_until);
			$error = '';
			Main::$current->redirect(
				'/ITRocks/Framework/Tools/Encryption/Sensitive_Data/Share', Target::MAIN
			);
		}
		$_POST['sensitive_password'] = '';
		Sensitive_Data::password();
		return $error
			? Message::display(new Cipher_Key, Loc::tr('Error'), Loc::tr($error))
			: Message::display(new Cipher_Key, Loc::tr('User now has access to sensitive data'));
	}

	//------------------------------------------------------------------------------------- sendEmail
	protected function sendEmail(User $user, string $token, Date_Time $valid_until) : void
	{
		/** @var $current_user User|Has_Name */
		/** @var $user User|Has_Name */
		$current_user      = User::current();
		$has_name          = isA($user, Has_Name::class);
		$current_user_name = ($has_name && $current_user->name)
			? $current_user->name
			: $current_user->login;
		$user_name = ($has_name && $user->name) ? $user->name : $user->login;
		$service   = Paths::absoluteBase();
		$date      = Loc::dateToLocale($valid_until);
		$replace = [
			'$current_user_name' => $current_user_name,
			'$date'              => $date,
			'$service'           => $service,
			'$token'             => $token,
			'$user_name'         => $user_name
		];
		$email_template = file_get_contents(
			file_exists(__DIR__ . '/email-' . Loc::language() . '.html')
				? (__DIR__ . '/email-' . Loc::language() . '.html')
				: (__DIR__ . '/email-en.html')
		);
		/** @noinspection PhpUnhandledExceptionInspection valid */
		$email = Builder::create(Email::class);
		$email->subject = Loc::tr('Access to sensitive data') . SP . $service;
		$email->to      = [new Recipient($user->email, $user_name)];
		$email->copy_to = [new Recipient(User::current()->email, $current_user_name)];
		$email->content = strReplace($replace, $email_template);
		$sender = (Sender\Smtp::get(false) ?: Sender\File::get(false));
		$sender->send($email);
	}

}
