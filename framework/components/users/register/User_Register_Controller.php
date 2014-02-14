<?php
namespace SAF\Framework;

/**
 * The user register controller offers a registration form view
 */
class User_Register_Controller implements Feature_Controller
{

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $class_name string
	 * @return mixed[]
	 */
	protected function getViewParameters(
		Controller_Parameters $parameters,
		/** @noinspection PhpUnusedParameterInspection */ $form,
		$class_name
	) {
		$parameters = $parameters->getObjects();
		$object = reset($parameters);
		if (empty($object) || !is_object($object) || (get_class($object) !== $class_name)) {
			$object = Builder::create($class_name);
			$parameters = array_merge(array($class_name => $object), $parameters);
		}
		return $parameters;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$class_name = 'SAF\Framework\User';
		$current = User::current();
		if ($current) {
			User_Authentication::disconnect(User::current());
		}
		$parameters = $this->getViewParameters($parameters, $form, $class_name);
		if (isset($form["login"]) && isset($form["password"])) {
			$user = null;
			$errors_messages = User_Authentication::controlRegisterFormParameters($form);
			if (!$errors_messages && empty($errors_messages)) {
				if (User_Authentication::controlNameNotUsed($form["login"])) {
					$user = User_Authentication::register($form);
				}
			}
			if ($user) {
				return View::run($parameters, $form, $files, $class_name, "registerConfirm");
			}
			else {
				$parameters["errors"] = $errors_messages;
				return View::run($parameters, $form, $files, $class_name, "registerError");
			}
		}
		else {
			$parameters["inputs"] = User_Authentication::getRegisterInputs();
			return View::run($parameters, $form, $files, $class_name, "register");
		}
	}

}
