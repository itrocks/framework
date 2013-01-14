<?php
namespace SAF\Framework;

class Default_Output_Controller extends Output_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	protected function getGeneralButtons($object)
	{
		return array(
			new Button(
				"Duplicate", View::link($object, "duplicate"), "duplicate"
			)
		);
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * prepare method for default "form-typed" output view controller
	 *
	 * @param Controller_Parameters $parameters
	 * @param string $class_name
	 */
	protected function getViewParameters(Controller_Parameters $parameters, $class_name)
	{
		$parameters = $parameters->getObjects();
		$object = reset($parameters);
		if (!$object || !is_object($object) || (get_class($object) !== $class_name)) {
			$object = new $class_name();
			$parameters = array_merge(array($class_name => $object), $parameters);
		}
		$parameters["general_buttons"]   = $this->getGeneralButtons($object);
		$parameters["properties_filter"] = $this->getPropertiesList($class_name);
		$parameters["tabs"]              = $this->getTabs($object, $parameters["properties_filter"]);
		return $parameters;
	}

}
