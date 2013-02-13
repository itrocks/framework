<?php
namespace SAF\Framework;

class Property_Select_Controller implements Controller
{

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @param $class Reflection_Class
	 * @param $path  string
	 * @return Reflection_Property_Value[]
	 */
	public function getProperties(Reflection_Class $class, $path = null)
	{
		$properties = array();
		foreach ($class->getAllProperties() as $property) {
			$property = new Reflection_Property_Value($property);
			$property->path = isset($path) ? $path . "." . $property->name : $property->name;
			$properties[] = $property;
		}
		return $properties;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Property select controller, starting from a given root class
	 *
	 * @param $parameters Controller_Parameters
	 * - first : the reference class name (ie a business object)
	 * - second : if set, the selected property name into the reference class name
	 * @param $form array  not used
	 * @param $files array not used
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$property = new Property();
		$class_name = $parameters->shiftUnnamed();
		$property_path = $parameters->shiftUnnamed();
		$property->class = Reflection_Class::getInstanceOf(
			Set::elementClassNameOf(Namespaces::fullClassName($class_name))
		);
		if (!empty($property_path)) {
			$property->name = rLastParse($property_path, ".", 1, true);
			$parameters->set("container", "subtree");
		}
		else {
			$property_path = null;
		}
		$objects = $parameters->getObjects();
		array_unshift($objects, $property);
		$objects["properties"] = $this->getProperties($property->class, $property_path);
		/**
		 * Objects for the view :
		 * first        Property the property object (with selected property name, or not)
		 * "properties" Reflection_Property[] all properties from the reference class
		 */
		return View::run($objects, $form, $files, get_class($property), "select");
	}

}
