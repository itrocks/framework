<?php
namespace ITRocks\Framework\Tools\Encryption\Sensitive_Data\Cipher_Key;

use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Message;
use ITRocks\Framework\Feature\Save\Controller;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data\Cipher_Key;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data\Key;
use ITRocks\Framework\User;

/** @noinspection PhpUnused Controller */
class Save_Controller extends Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Parameters $parameters, array $form, array $files, $class_name) : string
	{
		if (!Sensitive_Data::isPasswordGlobalAndValid() && Dao::count(Key::class)) {
			$error = 'Bad cipher key';
		}
		elseif (strlen($form['new_cipher_key']) < 8) {
			$error = 'Cipher key must be at least 8 characters long';
		}
		elseif ($form['new_cipher_key'] !== $form['confirm_cipher_key']) {
			$error = 'Confirmed cipher key does not match cipher key';
		}
		else {
			/** @var $key Key */
			$key    = Dao::searchOne(['user' => User::current()], Key::class);
			$secret = $key->getSecret();
			$_POST['sensitive_password'] = $form['new_cipher_key'];
			$key->setSecret($secret);
			Dao::write($key, Dao::only('secret'));
			$_POST['sensitive_password'] = '';
			Sensitive_Data::password();
			$error = '';
			Main::$current->redirect(
				'/ITRocks/Framework/Tools/Encryption/Sensitive_Data/Cipher_Key', Target::MAIN
			);
		}
		return $error
			? Message::display(new Cipher_Key, Loc::tr('Error'), Loc::tr($error))
			: Message::display(new Cipher_Key, Loc::tr('New cipher key saved'));
	}

}
