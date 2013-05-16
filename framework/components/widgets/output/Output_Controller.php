<?php
namespace SAF\Framework;

abstract class Output_Controller implements Default_Feature_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $class_name string
	 * @param $parameters string[]
	 * @return Button[]
	 */
	protected function getGeneralButtons($class_name, $parameters)
	{
		return array();
	}

	//----------------------------------------------------------------------------- getPropertiesList
	/**
	 * @param $class_name string
	 * @return string[] property names list
	 */
	protected function getPropertiesList($class_name)
	{
		return null;
	}

	//--------------------------------------------------------------------------------------- getTabs
	/**
	 * Get output tabs list for a given object
	 *
	 * @param $object object
	 * @param $properties string[] Can be null
	 * @return Tab[]
	 */
	protected function getTabs($object, $properties)
	{
		$tab = new Tab("main");
		$tab->includes = Tabs_Builder_Object::buildObject($object, $properties);
		return $tab;
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $class_name string
	 * @return mixed[]
	 */
	protected function getViewParameters(Controller_Parameters $parameters, $form, $class_name)
	{
		$parameters = $parameters->getObjects();
		$object = reset($parameters);
		if (empty($object) || !is_object($object) || (get_class($object) !== $class_name)) {
			$object = new $class_name();
			$parameters = array_merge(array($class_name => $object), $parameters);
		}
		$parameters["general_buttons"]   = $this->getGeneralButtons($object, $parameters);
		$parameters["properties_filter"] = $this->getPropertiesList($class_name);
		$parameters["tabs"]              = $this->getTabs($object, $parameters["properties_filter"]);
		return $parameters;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default output view controller
	 *
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = $this->getViewParameters($parameters, $form, $class_name);
		if (!isset($parameters["feature"])) {
			$parameters["feature"] = "output";
		}
		return View::run($parameters, $form, $files, $class_name, "output");
	}

}
