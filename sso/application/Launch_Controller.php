<?php
namespace SAF\Framework\SSO\Application;

use SAF\Framework\Controller;
use SAF\Framework\Controller\Default_Controller;
use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Session;
use SAF\Framework\SSO\Application;
use SAF\Framework\SSO\Authentication_Server;
use SAF\Framework\Tools\Paths;
use SAF\Framework\User;
use SAF\Framework\View;

/**
 * Launch an application authenticated through SSO
 */
class Launch_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		$name = $parameters->uri->parameters->getRawParameter('name');
		if (isset($name) && !empty($name)) {
			/** @var $auth_server Authentication_Server */
			$auth_server = Session::current()->plugins->get(Authentication_Server::class);
			if ($application = $auth_server->hasApplication($name)) {
				$parameters = $parameters->getObjects();
				$parameters['uri_to_launch'] = $application->uri;
				$parameters['token'] = $auth_server->getToken();
				$parameters['login'] = User::current()->login;
				$parameters['server'] = Paths::getUrl();
				return View::run($parameters, $form, $files, Application::class, 'launch');
			}
		}
		return (new Default_Controller)->run($parameters, [], [], User::class, Controller\Feature::F_DENIED);
	}

}
