<?php
namespace ITRocks\Framework\SSO\Authentication;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Session;
use ITRocks\Framework\SSO\Authentication_Server;
use ITRocks\Framework\User;
use stdClass;

/**
 * Launch an application authenticated through SSO
 */
class Check_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------- CHECK_FEATURE
	const CHECK_FEATURE = 'check';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		header('Content-Type: application/json');

		$login = $parameters->uri->parameters->getRawParameter('login');
		$login = $login
			?: ((isset($form['login']) && is_string($form['login'])) ? $form['login'] : '');
		$token = $parameters->uri->parameters->getRawParameter('token');
		$token = $token
			?: ((isset($form['token']) && is_string($form['token'])) ? $form['token'] : '');
		$sentence = $parameters->uri->parameters->getRawParameter('sentence');
		$sentence = $sentence
			?: ((isset($form['sentence']) && is_string($form['sentence'])) ? $form['sentence'] : '');

		/** @var $auth_server Authentication_Server */
		$auth_server   = Session::current()->plugins->get(Authentication_Server::class);
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
		$object = new stdClass();
		if ($authenticated) {
			$object->response     = 'OK';
			$object->session_id   = session_id();
			$object->session_name = session_name();
		}
		else {
			header($_SERVER['SERVER_PROTOCOL'] . SP . '403 Forbidden', true, 403);
			$object->response = 'Forbidden';
		}
		/** @noinspection PhpUnhandledExceptionInspection */
		return jsonEncode($object);
	}

}
