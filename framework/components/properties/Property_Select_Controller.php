<?php
namespace SAF\Framework;

class Property_Select_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @param $class Reflection_Class
	 * @return Reflection_Property[]
	 */
	public function getProperties(Reflection_Class $class)
	{
		return $class->getAllProperties();
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
		$objects = $parameters->getObjects();
		$property->class = Reflection_Class::getInstanceOf(
			Set::elementClassNameOf($parameters->shiftUnnamed($objects))
		);
		if ($objects) {
			$property->name = $parameters->shiftUnnamed($objects);
		}
		array_unshift($objects, $property);
		$objects["properties"] = $this->getProperties($property->class);
		/**
		 * Objects for the view :
		 * first        Property the property object (with selected property name, or not)
		 * "properties" Reflection_Property[] all properties from the reference class
		 */
		View::run($objects, $form, $files, get_class($property), "select");
	}

}
