<?php
namespace ITRocks\Framework\Property;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Property;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\List_Annotation;
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

	//--------------------------------------------------------------------------- $composite_property
	/**
	 * @var Reflection_Property
	 */
	private $composite_property = null;

	//---------------------------------------------------------------------- $composite_link_property
	/**
	 * @var Reflection_Property
	 */
	private $composite_link_property = null;

	//------------------------------------------------------------------------------ filterProperties
	/**
	 * Filter a list of properties by removing properties that should not be visible
	 *
	 * @param $source_properties Reflection_Property_Value[]
	 * @return Reflection_Property_Value[]
	 */
	private function filterProperties(array $source_properties)
	{
		$properties = [];
		/** @var $source_properties Reflection_Property[] */
		$source_properties = Replaces_Annotations::removeReplacedProperties($source_properties);
		foreach ($source_properties as $property_name => $property) {
			if (
				(!$this->composite_property || ($property->name !== $this->composite_property->name))
				&& (
					!$this->composite_link_property
					|| ($property->name !== $this->composite_link_property->name)
				)
				&& $property->isPublic()
				&& $property->isVisible(false)
			) {
				$properties[$property_name] = $property;
			}
		}
		return $properties;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Get list of properties to display for a class
	 *
	 * @param $class                Reflection_Class
	 * @param $composite_class_name string
	 * @return Reflection_Property_Value[]
	 */
	protected function getProperties(Reflection_Class $class, $composite_class_name = null)
	{
		if (isset($composite_class_name) && isA($class->name, Component::class)) {
			$composite_properties = call_user_func(
				[$class->name, 'getCompositeProperties'],
				$composite_class_name
			);
			$this->composite_property = reset($composite_properties);
		}
		else {
			$this->composite_property = null;
		}
		if (Link_Annotation::of($class)->value) {
			$link_class                    = new Link_Class($class->name);
			$this->composite_link_property = $link_class->getCompositeProperty();
			$properties = $this->filterProperties($link_class->getProperties([T_EXTENDS, T_USE]));
		}
		else {
			$properties = $this->filterProperties($class->getProperties([T_EXTENDS, T_USE]));
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
	 * @param $form  array not used
	 * @param $files array[] not used
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		$class_name = Set::elementClassNameOf($parameters->shiftUnnamed());
		$class      = new Reflection_Class($class_name);
		if (List_Annotation::of($class)->has(List_Annotation::LOCK)) {
			return Loc::tr('You are not allowed to customize this list');
		}
		$property_path = $parameters->shiftUnnamed();
		if (empty($property_path)) {
			$top_property        = new Property();
			$top_property->class = $class_name;
			$properties          = $this->getProperties($class);
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
