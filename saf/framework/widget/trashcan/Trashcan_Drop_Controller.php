<?php
namespace SAF\Framework;

use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Main;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Tools\Namespaces;
use SAF\Framework\Tools\Set;

/**
 * This controller is called when objects are dropped into the trashcan
 */
class Trashcan_Drop_Controller implements Feature_Controller
{

	//---------------------------------------------------------------------------------- deleteObject
	/**
	 * Delete an object
	 *
	 * @param $parameters mixed[]
	 * - first : the deleted object
	 * - second : the feature name
	 * - other parameters are kept and sent to the object delete controller
	 * @return mixed
	 */
	private function deleteObject($parameters)
	{
		$object = array_shift($parameters);
		array_shift($parameters); // $feature
		$controller_uri = SL . get_class($object) . SL . Dao::getObjectIdentifier($object)
			. SL . 'delete';
		return (new Main())->runController($controller_uri, $parameters);
	}

	//----------------------------------------------------------------------------------- parseAndRun
	/**
	 * @param $parameters mixed[]
	 * @return mixed
	 */
	private function parseAndRun($parameters)
	{
		$first_parameter = array_shift($parameters);
		if (is_object($first_parameter)) {
			$context_class_name = get_class($first_parameter);
		}
		else {
			$context_class_name = Namespaces::fullClassName(Set::elementClassNameOf($first_parameter));
		}
		$context_feature = array_shift($parameters);
		$third_parameter = reset($parameters);
		if (is_object($third_parameter)) {
			return $this->deleteObject($parameters);
		}
		else {
			$class_name = array_shift($parameters);
			return $this->removeElement($class_name, $context_class_name, $context_feature, $parameters);
		}
	}

	//--------------------------------------------------------------------------------- removeElement
	/**
	 * Remove element(s) of a given class from context
	 *
	 * @param $class_name string The element class name
	 * @param $context_class_name string The context class where to remove the element from
	 * @param $context_feature string The context feature to remove the element from
	 * @param $parameters mixed[] The elements to be removed, and additional parameters
	 * @return mixed
	 */
	private function removeElement($class_name, $context_class_name, $context_feature, $parameters)
	{
		return (new Main())->runController(
			SL . $class_name . SL . 'remove' . SL . $context_class_name . SL . $context_feature, $parameters
		);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		$trash = $parameters->GetUnnamedParameters();
		$objects = $parameters->getObjects();
		return (count($trash) <= 1)
			? $this->deleteObject($objects)
			: $this->parseAndRun($objects);
	}

}
