<?php
namespace SAF\Framework;

class User_Register_Controller implements Feature_Controller
{
	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $parameters Controller_Parameters
	 * @param $class_name string
	 * @return mixed[]
	 */
	protected function getViewParameters(Controller_Parameters $parameters, $class_name)
	{
		$parameters = $parameters->getObjects();
		$object = reset($parameters);
		if (empty($object) || !is_object($object) || (get_class($object) !== $class_name)) {
			$object = new $class_name();
			$parameters = array_merge(array($class_name => $object), $parameters);
		}
		$parameters["inputs"] = User_Authentication::getRegisterInputs();
		return $parameters;
	}
	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$class_name = "\\SAF\\Framework\\User";
		$current = User::current();
		if ($current) {
			User_Authentication::disconnect(User::current());
		}
		$parameters = $this->getViewParameters($parameters, $class_name);
		if(isset($form["login"]) && isset($form["password"])){
			$user = null;
			if(User_Authentication::controlRegisterFormParameters($form)){
				if(User_Authentication::controlNameNotUsed($form["login"]))
					$user = User_Authentication::register($form["login"], $form["password"]);
			}
			if($user){
				View::run($parameters, $form, $files, $class_name, "registerConfirm");
			}
			else {
				View::run($parameters, $form, $files, $class_name, "registerError");
			}
		}
		else {
			View::run($parameters, $form, $files, $class_name, "register");
		}
	}

}
