<?php
namespace ITRocks\Framework\SSO\Application;

use ITRocks\Framework\Controller;
use ITRocks\Framework\Controller\Default_Controller;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Session;
use ITRocks\Framework\SSO\Application;
use ITRocks\Framework\SSO\Authentication;
use ITRocks\Framework\SSO\Authentication_Server;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\User;
use ITRocks\Framework\View;

/**
 * Launch an application authenticated through SSO
 */
class Launch_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$name = $parameters->uri->parameters->getRawParameter('name');
		if ($name) {
			/** @var $auth_server Authentication_Server */
			$auth_server = Session::current()->plugins->get(Authentication_Server::class);
			if ($application = $auth_server->hasApplication($name)) {
				$parameters = $parameters->getObjects();

				$parameters['login'] = User::current()->login;
				$parameters['path']  = SL . str_replace(BS, SL, Authentication::class)
					. SL . Authentication\Check_Controller::CHECK_FEATURE;
				$parameters['server']                      = Paths::getUrl();
				$parameters['third_party_application_uri'] = $application->uri;
				$parameters['token']                       = $auth_server->getToken();

				return View::run($parameters, $form, $files, Application::class, 'launch');
			}
		}
		return (new Default_Controller)->run(
			$parameters, [], [], User::class, Controller\Feature::F_DENIED
		);
	}

}
