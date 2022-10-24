<?php
namespace ITRocks\Framework\Email\Send;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Email;
use ITRocks\Framework\Email\Sender;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

/**
 * Send email controller
 */
class Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'send';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$email = $parameters->getMainObject(Email::class);
		if (!$email) {
			return null;
		}
		$sender = (Sender\Smtp::get() ?: Sender\File::get());
		if (!$sender) {
			return null;
		}
		$sender->send($email);
		$parameters->set(Template::TEMPLATE, 'sent');
		return View::run($parameters->getObjects(), $form, $files, get_class($email), static::FEATURE);
	}

}
