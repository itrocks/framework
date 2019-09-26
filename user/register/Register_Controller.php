<?php
namespace ITRocks\Framework\User\Register;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Authenticate\Authentication;
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
	 * @return mixed[]
	 */
	protected function getViewParameters(
		Parameters $parameters,
		/** @noinspection PhpUnusedParameterInspection */ array $form,
		$class_name
	) {
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
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
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
			$errors_messages = Authentication::controlRegisterFormParameters($form);
			if (!$errors_messages && empty($errors_messages)) {
				if (Authentication::controlNameNotUsed($form['login'])) {
					$user = Authentication::register($form);
				}
			}
			if ($user) {
				$parameters[Template::TEMPLATE] = 'confirm';
				return View::run($parameters, $form, $files, User::class, 'register');
			}
			else {
				$parameters['errors'] = $errors_messages;
				$parameters[Template::TEMPLATE] = 'error';
				return View::run($parameters, $form, $files, User::class, 'register');
			}
		}
		else {
			$parameters['inputs'] = Authentication::getRegisterInputs();
			return View::run($parameters, $form, $files, User::class, 'register');
		}
	}

}
