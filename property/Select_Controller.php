<?php
namespace ITRocks\Framework\Property;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Property;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Set;
use ITRocks\Framework\View;

/**
 * The property select controller is a class properties tree view controller.
 * It lists all properties from a class, display their names, and enable the user to drag them.
 */
class Select_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @param $class                   Reflection_Class
	 * @param $composite_class_name    string
	 * @return Reflection_Property_Value[]
	 */
	protected function getProperties(Reflection_Class $class, $composite_class_name = null)
	{
		$properties = [];
		if (isset($composite_class_name) && isA($class->name, Component::class)) {
			$composite_property = call_user_func(
				[$class->name, 'getCompositeProperties'],
				$composite_class_name
			);
			$composite_property = reset($composite_property);
		}
		else {
			$composite_property = null;
		}
		if ($class->getAnnotation('link')->value) {
			$link_class = new Link_Class($class->name);
			$composite_link_property = $link_class->getCompositeProperty();
			/** @var $source_properties Reflection_Property[] */
			$source_properties = Replaces_Annotations::removeReplacedProperties(
				$link_class->getProperties([T_EXTENDS, T_USE])
			);
			foreach ($source_properties as $property_name => $property) {
				if (
					(empty($composite_property) || ($property->name !== $composite_property->name))
					&& (!$composite_link_property || ($property->name !== $composite_link_property->name))
					&& !$property->isStatic()
					&& !$property->getListAnnotation(User_Annotation::ANNOTATION)->has(
						User_Annotation::INVISIBLE
					)
				) {
					$properties[$property_name] = $property;
				}
			}
		}
		else {
			/** @var $source_properties Reflection_Property[] */
			$source_properties = Replaces_Annotations::removeReplacedProperties(
				$class->getProperties([T_EXTENDS, T_USE])
			);
			foreach ($source_properties as $property_name => $property) {
				if (
					(empty($composite_property) || ($property->name !== $composite_property->name))
					&& !$property->isStatic()
					&& !$property->getListAnnotation(User_Annotation::ANNOTATION)->has(
						User_Annotation::INVISIBLE
					)
				) {
					$properties[$property_name] = $property;
				}
			}
		}
		return $properties;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Property select controller, starting from a given root class
	 *
	 * @param $parameters Parameters
	 * - first : the root reference class name (ie a business object)
	 * - second : if set, the selected property path into the root reference class name
	 * @param $form array  not used
	 * @param $files array not used
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		$class_name = Set::elementClassNameOf($parameters->shiftUnnamed());
		$property_path = $parameters->shiftUnnamed();
		if (empty($property_path)) {
			$top_property        = new Property();
			$top_property->class = $class_name;
			$properties          = $this->getProperties(new Reflection_Class($class_name));
			foreach ($properties as $property) {
				$property->path = $property->name;
			}
		}
		else {
			$top_property = new Reflection_Property($class_name, $property_path);
			if ($top_property->getType()->isClass()) {
				$properties = $this->getProperties(
					new Reflection_Class($top_property->getType()->getElementTypeAsString()),
					$top_property->final_class
				);
				foreach ($properties as $property) {
					$property->path = $property_path . DOT . $property->name;
				}
				if (!$parameters->getRawParameter(Parameter::CONTAINER)) {
					$parameters->set(Parameter::CONTAINER, 'subtree');
				}
			}
			else {
				$properties = [];
			}
		}
		$objects = $parameters->getObjects();
		array_unshift($objects, $top_property);
		$objects['properties']        = $properties;
		$objects['class_name']        = $class_name;
		$objects['display_full_path'] = false;
		/**
		 * Objects for the view :
		 * first        Property the property object (with selected property name, or not)
		 * 'properties' Reflection_Property[] all properties from the reference class
		 */
		return View::run($objects, $form, $files, Property::class, 'select');
	}

}
