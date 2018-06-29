<?php
namespace ITRocks\Framework\Property;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Property;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\List_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Set;
use ITRocks\Framework\View;
use ReflectionException;

/**
 * The property select controller is a class properties tree view controller.
 * It lists all properties from a class, display their names, and enable the user to drag them.
 */
class Select_Controller implements Feature_Controller
{

	//---------------------------------------------------------------------- $composite_link_property
	/**
	 * @var Reflection\Reflection_Property
	 */
	private $composite_link_property = null;

	//--------------------------------------------------------------------------- $composite_property
	/**
	 * @var Reflection\Reflection_Property
	 */
	private $composite_property = null;

	//------------------------------------------------------------------------------ filterProperties
	/**
	 * Filter a list of properties by removing properties that should not be visible
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $source_properties Reflection\Reflection_Property[]
	 * @param $display_full_path boolean
	 * @return Reflection_Property[]
	 */
	private function filterProperties(array $source_properties, $display_full_path = false)
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
				&& $property->isVisible(false, false)
			) {
				/** @noinspection PhpUnhandledExceptionInspection valid $property */
				$properties[$property_name] = new Reflection_Property(
					$property->root_class, $property->name
				);
				$properties[$property_name]->display = Loc::tr(Names::propertyToDisplay(
					$display_full_path ? $properties[$property_name]->path : $properties[$property_name]->name
				));
			}
		}
		return $properties;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Get list of properties to display for a class
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class                Reflection_Class
	 * @param $composite_class_name string
	 * @param $display_full_path    boolean
	 * @return Reflection_Property[]
	 */
	protected function getProperties(
		Reflection_Class $class, $composite_class_name = null, $display_full_path = false
	) {
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
			/** @noinspection PhpUnhandledExceptionInspection valid $class */
			$link_class                    = new Link_Class($class->name);
			$this->composite_link_property = $link_class->getCompositeProperty();
			$properties                    = $link_class->getProperties([T_EXTENDS, T_USE]);
		}
		else {
			$properties = $class->getProperties([T_EXTENDS, T_USE]);
		}
		$properties         = $this->filterProperties($properties, $display_full_path);
		/*
		// TODO WIP
		$reverse_properties = $this->getReverseProperties($class);
		if ($reverse_properties) {
			$properties[] = null;
			$properties   = array_merge($properties, $reverse_properties);
		}
		*/
		return $properties;
	}

	//-------------------------------------------------------------------------- getReverseProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class Reflection_Class
	 * @return Reflection_Property[]
	 */
	protected function getReverseProperties(Reflection_Class $class)
	{
		// class and its parents
		$base_class  = Builder::current()->sourceClassName($class->name);
		$class_name  = $class->name;
		$class_names = [$class_name];
		while ($class_name && ($class_name !== $base_class)) {
			if ($class_name = get_parent_class($class_name)) {
				$class_names[] = $class_name;
			}
		}

		// dependencies : which properties point to the class ?
		/** @noinspection PhpUnhandledExceptionInspection valid class names */
		$properties = Dependency::propertiesUsingClass($class_names);

		// filter and add properties
		$properties = $this->filterProperties($properties);
		foreach ($properties as $property_path => $property) {
			$property_class      = $property->getRootClass();
			$property_class_name = $property_class->name;
			if (
				Link_Annotation::of($property_class)->value
				|| $property->getAnnotation('composite')->value
			) {
				unset($properties[$property_path]);
			}
			else {
				$property->display = Loc::tr(Names::classToDisplay($property_class_name))
					. SP . '(' . Loc::tr($property->name) . ')';
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
	 * @param $form  array not used
	 * @param $files array[] not used
	 * @return mixed
	 * @throws ReflectionException
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		$class_name = Set::elementClassNameOf($parameters->shiftUnnamed());
		/** @noinspection PhpUnhandledExceptionInspection $class_name is always valid */
		$class = new Reflection_Class($class_name);
		if (List_Annotation::of($class)->has(List_Annotation::LOCK)) {
			return Loc::tr('You are not allowed to customize this list');
		}
		$property_path = $parameters->shiftUnnamed();
		if (empty($property_path)) {
			$top_property        = new Property();
			$top_property->class = $class_name;
			$properties          = $this->getProperties($class);
			foreach ($properties as $property) {
				if ($property) {
					$property->path = $property->name;
				}
			}
		}
		else {
			$top_property = new Reflection\Reflection_Property($class_name, $property_path);
			if ($top_property->getType()->isClass()) {
				/** @noinspection PhpUnhandledExceptionInspection $top_property already tested */
				$properties = $this->getProperties(
					new Reflection_Class($top_property->getType()->getElementTypeAsString()),
					$top_property->final_class
				);
				foreach ($properties as $property) {
					if ($property) {
						$property->path = $property_path . DOT . $property->name;
					}
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
		$objects['properties'] = $properties;
		$objects['class_name'] = $class_name;
		/**
		 * Objects for the view :
		 * first        Property the property object (with selected property name, or not)
		 * 'properties' Reflection_Property[] all properties from the reference class
		 */
		return View::run($objects, $form, $files, Property::class, 'select');
	}

}
