<?php
namespace SAF\Framework;

class Trashcan_Drop_Controller implements Feature_Controller
{

	//---------------------------------------------------------------------------------- deleteObject
	/**
	 * Delete an object
	 *
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

	//----------------------------------------------------------------------------------- parseAndRun
	/**
	 * @param multitype:mixed $parameters
	 */
	private function parseAndRun($parameters)
	{
		$first_parameter = array_shift($parameters);
		if (is_object($first_parameter)) {
			$context_class_name = get_class($first_parameter);
		}
		else {
			$context_class_name = Set::elementClassNameOf($first_parameter);
		}
		$context_feature = array_shift($parameters);
		$third_parameter = reset($parameters);
		if (is_object($third_parameter)) {
			$this->deleteObject($parameters);
		}
		else {
			$class_name = array_shift($parameters);
			$this->removeElement($class_name, $context_class_name, $context_feature, $parameters);
		}
	}

	//--------------------------------------------------------------------------------- removeElement
	/**
	 * Remove element(s) of a given class from context
	 *
	 * @param string $class_name The element class name
	 * @param string $context_class_name The context class where to remove the element from
	 * @param string $context_feature The context feature to remove the element from
	 * @param multitype:mixed $parameters The elements to be removed, and additional parameters
	 */
	private function removeElement($class_name, $context_class_name, $context_feature, $parameters)
	{
		Main_Controller::getInstance()->runController(
			"/" . $class_name . "/remove/" . $context_class_name . "/" . $context_feature, $parameters
		);
	}

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$trash = $parameters->GetUnnamedParameters();
		$parameters = $parameters->getObjects();
		if (count($trash) <= 1) {
			$this->deleteObject($parameters);
		}
		else {
			$this->parseAndRun($parameters);
		}
	}

}
