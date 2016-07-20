<?php
namespace SAF\Framework\SSO\Authentication;

use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Parameter;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Session;
use SAF\Framework\SSO\Authentication;
use SAF\Framework\SSO\Authentication_Server;
use SAF\Framework\User;
use SAF\Framework\View;

/**
 * Launch an application authenticated through SSO
 */
class Check_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------- CHECK_FEATURE
	const CHECK_FEATURE = 'check';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		header('Content-Type: application/json');

		$login = $parameters->uri->parameters->getRawParameter('login');
		$login = $login ?: (isset($form['login']) ? $form['login'] : '');
		$token = $parameters->uri->parameters->getRawParameter('token');
		$token = $token ?: (isset($form['token']) ? $form['token'] : '');
		$sentence = $parameters->uri->parameters->getRawParameter('sentence');
		$sentence = $sentence ?: (isset($form['sentence']) ? $form['sentence'] : '');

		/** @var $auth_server Authentication_Server */
		$auth_server = Session::current()->plugins->get(Authentication_Server::class);
		$authenticated = false;

		// check that sentence comes from a valid application
		if ($application = $auth_server->getApplicationBySentence($sentence)) {
			// check that login exists
			if (!User\Authenticate\Authentication::controlNameNotUsed($login)) {
				// check that token match for a user connection
				if ($auth_server->validateToken($application, $login, $token)) {
					$authenticated = true;
				}
			}
		}
		$object = new \stdClass();
		if ($authenticated) {
			$object->response = 'OK';
			$object->session_name = session_name();
			$object->session_id = session_id();
			return json_encode($object);
		}
		else {
			header($_SERVER["SERVER_PROTOCOL"].' 403 Forbidden', true, 403);
			$object->response = 'Forbidden';
			return json_encode($object);
		}
	}

}
