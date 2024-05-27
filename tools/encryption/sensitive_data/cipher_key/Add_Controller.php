<?php
namespace ITRocks\Framework\Tools\Encryption\Sensitive_Data\Cipher_Key;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Feature\Add\Controller;
use ITRocks\Framework\Setting;

/** @noinspection PhpUnused Controller */
class Add_Controller extends Controller
{

	public function getGeneralButtons($object, array $parameters, Setting\Custom\Set $settings = null)
		: array
	{
		$buttons = parent::getGeneralButtons($object, $parameters, $settings);
		unset($buttons[Feature::F_CLOSE]);
		return $buttons;
	}

}
