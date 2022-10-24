<?php
namespace ITRocks\Framework\Component\Trashcan;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Set;
use ITRocks\Framework\View;

/**
 * This controller is called when objects are dropped into the trashcan
 */
class Drop_Controller implements Feature_Controller
{

	//---------------------------------------------------------------------------------- deleteObject
	/**
	 * Delete an object
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters array
	 * - first : the deleted object
	 * - other parameters are not sent to the delete controller (only as_widget is kept)
	 * @return mixed
	 */
	private function deleteObject(array $parameters)
	{
		$object = array_shift($parameters);
		/** @noinspection PhpUnhandledExceptionInspection get_class($object) is valid */
		$controller_uri = SL . Names::classToUri(get_class($object))
			. SL . Dao::getObjectIdentifier($object)
			. SL . Feature::F_DELETE;
		return (new Main())->runController($controller_uri, $parameters);
	}

	//----------------------------------------------------------------------------------- parseAndRun
	/**
	 * @param $parameters array
	 * @return mixed
	 */
	private function parseAndRun(array $parameters)
	{
		$first_parameter = array_shift($parameters);
		if (is_object($first_parameter)) {
			$context_class_name = get_class($first_parameter);
		}
		else {
			$context_class_name = Builder::current()->sourceClassName(
				Set::elementClassNameOf($first_parameter)
			);
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
	private function removeElement(
		$class_name, $context_class_name, $context_feature, array $parameters
	) {
		$context = substr(View::link($context_class_name, [$context_feature]), 1);
		return (new Main)->runController(
			View::link($class_name, Feature::F_REMOVE, [$context]),
			$parameters
		);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$trash   = $parameters->getUnnamedParameters();
		$objects = $parameters->getObjects();
		return (count($trash) <= 1)
			? $this->deleteObject($objects)
			: $this->parseAndRun($objects);
	}

}
