<?php
namespace SAF\Framework;

/**
 * The property select controller is a class properties tree view controller.
 * It lists all properties from a class, display their names, and enable the user to drag them.
 */
class Property_Select_Controller implements Controller
{

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @param $class                   Reflection_Class
	 * @param $composite_class_name    string
	 * @return Reflection_Property_Value[]
	 */
	public function getProperties(Reflection_Class $class, $composite_class_name = null)
	{
		$properties = array();
		if (isset($composite_class_name) && class_uses_trait($class->name, 'SAF\Framework\Component')) {
			$composite_property = call_user_func(
				array($class->name, "getCompositeProperties"),
				$composite_class_name
			);
			$composite_property = reset($composite_property);
		}
		else {
			$composite_property = null;
		}
		if ($class->getAnnotation("link")->value) {
			$link_class = new Link_Class($class->name);
			$composite_link_property = $link_class->getCompositeProperty();
			foreach ($link_class->getAllProperties() as $property) {
				if (
					(!$composite_property || ($property->name !== $composite_property->name))
					&& (!$composite_link_property || ($property->name !== $composite_link_property->name))
					&& !$property->isStatic()
				) {
					$properties[] = $property;
				}
			}
		}
		else {
			foreach ($class->getAllProperties() as $property) {
				if (
					(empty($composite_property) || ($property->name !== $composite_property->name))
					&& !$property->isStatic()
				) {
					$properties[] = $property;
				}
			}
		}
		return $properties;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Property select controller, starting from a given root class
	 *
	 * @param $parameters Controller_Parameters
	 * - first : the root reference class name (ie a business object)
	 * - second : if set, the selected property path into the root reference class name
	 * @param $form array  not used
	 * @param $files array not used
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$class_name = Namespaces::fullClassName(Set::elementClassNameOf($parameters->shiftUnnamed()));
		$property_path = $parameters->shiftUnnamed();
		if (empty($property_path)) {
			$top_property = new Property();
			$top_property->class = $class_name;
			$properties = $this->getProperties(new Reflection_Class($class_name));
			foreach ($properties as $property) {
				$property->path = $property->name;
			}
		}
		else {
			$top_property = new Reflection_Property($class_name, $property_path);
			$properties = $this->getProperties(
				new Reflection_Class(Builder::className($top_property->getType()->getElementTypeAsString())),
				$top_property->final_class
			);
			foreach ($properties as $property) {
				$property->path = $property_path . "." . $property->name;
			}
			$parameters->set("container", "subtree");
		}
		$objects = $parameters->getObjects();
		array_unshift($objects, $top_property);
		$objects["properties"] = $properties;
		$objects["class_name"] = $class_name;
		/**
		 * Objects for the view :
		 * first        Property the property object (with selected property name, or not)
		 * "properties" Reflection_Property[] all properties from the reference class
		 */
		return View::run($objects, $form, $files, 'SAF\Framework\Property', "select");
	}

}
