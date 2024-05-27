<?php
namespace ITRocks\Framework\Tools\Encryption\Sensitive_Data\Cipher_Key;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Add\Controller;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data\Cipher_Key;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data\Key;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data\Share;
use ITRocks\Framework\View;

/** @noinspection PhpUnused Controller */
class Add_Controller extends Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	public function getGeneralButtons($object, array $parameters, Setting\Custom\Set $settings = null)
		: array
	{
		$buttons = parent::getGeneralButtons($object, $parameters, $settings);
		unset($buttons[Feature::F_CLOSE]);
		$buttons['share'] = new Button(
			'Share', View::link(Share::class), Feature::F_ADD, Target::MAIN
		);
		return $buttons;
	}

	//------------------------------------------------------------------------------------------- run
	public function run(Parameters $parameters, array $form, array $files, $class_name) : string
	{
		$cipher_key        = $parameters->getMainObject(Cipher_Key::class);
		$cipher_key->token = $parameters->getRawParameter('token');
		if ($cipher_key->token || !Dao::count(Key::class)) {
			/** @noinspection PhpUnhandledExceptionInspection object */
			$sensitive_password = new Reflection_Property($cipher_key, 'sensitive_password');
			User::of($sensitive_password)->add(User::INVISIBLE);
		}
		return parent::run($parameters, $form, $files, $class_name);
	}

}
