<?php
namespace ITRocks\Framework\Tools\Encryption\Sensitive_Data\Share;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Feature\Add\Controller;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data\Cipher_Key;
use ITRocks\Framework\View;

/** @noinspection PhpUnused Controller */
class Add_Controller extends Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	public function getGeneralButtons($object, array $parameters, Setting\Custom\Set $settings = null)
		: array
	{
		$buttons = parent::getGeneralButtons($object, $parameters, $settings);
		$buttons[Feature::F_CLOSE]->link = View::link(Cipher_Key::class);
		return $buttons;
	}

}
