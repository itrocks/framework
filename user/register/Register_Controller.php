<?php
namespace ITRocks\Framework\User\Register;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Authenticate\Authentication;
use ITRocks\Framework\User\Authenticate\By_Token;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

/**
 * The user register controller offers a registration form view
 */
class Register_Controller implements Feature_Controller
{

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $class_name string
	 * @return array
	 */
	protected function getViewParameters(
		Parameters $parameters,
		/** @noinspection PhpUnusedParameterInspection */ array $form,
		string $class_name
	) : array
	{
		$parameters = $parameters->getObjects();
		$object = reset($parameters);
		if (empty($object) || !is_object($object) || !is_a($object, $class_name, true)) {
			/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
			$object = Builder::create($class_name);
			$parameters = array_merge([$class_name => $object], $parameters);
		}
		return $parameters;
	}

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
		$current = User::current();
		if ($current) {
			Authentication::disconnect();
		}
		$parameters = $this->getViewParameters($parameters, $form, User::class);
		if (
			isset($form['login']) && is_string($form['login'])
			&& isset($form['password']) && is_string($form['password'])
		) {
			$user = null;
			$error_messages = Authentication::controlRegisterFormParameters($form);
			if (!$error_messages) {
				if (Authentication::controlNameNotUsed($form['login'])) {
					$user = Authentication::register($form);
					if ($form['newToken'] ?? false) {
						/** @noinspection PhpUnhandledExceptionInspection class */
						$token = Builder::create(By_Token::class)->newToken($user, 'rt', true);
						return 'OK:TOKEN:[' . $token->code . ']';
					}
				}
				else {
					$error_messages[] = [
						'name'    => 'Login already used',
						'message' => 'Please choose another nickname for login'
					];
				}
			}
			if ($user) {
				$parameters[User::class]        = $user;
				$parameters[Template::TEMPLATE] = 'confirm';
			}
			else {
				$parameters['errors'] = $error_messages;
				$parameters[Template::TEMPLATE] = 'error';
			}
		}
		else {
			$parameters['inputs'] = Authentication::getRegisterInputs();
		}
		return View::run($parameters, $form, $files, User::class, 'register');
	}

}
