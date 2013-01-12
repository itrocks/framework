<?php
namespace SAF\Framework;

class Trashcan_Drop_Controller implements Feature_Controller
{

	//---------------------------------------------------------------------------------- deleteObject
	/**
	 * @param object $object
	 */
	private function deleteObject($parameters)
	{
		$object = array_shift($parameters);
		$feature = array_shift($parameters);
		$controller_uri = "/" . Namespaces::shortClassName(get_class($object))
			. "/" . Dao::getObjectIdentifier($object) . "/delete";
		Main_Controller::getInstance()->runController($controller_uri, $parameters);
	}

	//------------------------------------------------------------------------------ deleteProperties
	/**
	 * @param multitype:mixed $parameters
	 */
	private function deleteProperties($parameters)
	{
		$class_name = array_shift($parameters);
		if (is_object($class_name)) {
			$class_name = Namespaces::shortClassName(get_class($class_name));
		}
		$feature = array_shift($parameters);
		$get = array();
		foreach ($parameters as $key => $value) {
			if (is_numeric($value) || !is_numeric($key)) {
				unset($parameters[$key]);
				$get[$key] = $value;
			}
		}
		$elements = join("/", $parameters);
		Main_Controller::getInstance()->runController(
			"/" . $class_name . "/" . $feature . "Remove/" . $elements, $get
		);
	}

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$parameters = $parameters->getObjects();
		$object = reset($parameters);
		if (is_object($object) && !isset($parameters[1])) {
			$this->deleteObject($parameters);
		}
		else {
			$this->deleteProperties($parameters);
		}
	}

}
