<?php
namespace ITRocks\Framework\Property;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Property;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Class_\Display;
use ITRocks\Framework\Reflection\Attribute\Class_\Displays;
use ITRocks\Framework\Reflection\Attribute\Class_\List_;
use ITRocks\Framework\Reflection\Attribute\Property\Composite;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
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
	protected ?Reflection\Reflection_Property $composite_link_property = null;

	//--------------------------------------------------------------------------- $composite_property
	protected ?Reflection\Reflection_Property $composite_property = null;

	//------------------------------------------------------------------------------------------ $for
	#[Values(Feature::class)]
	protected ?string $for = null;

	//----------------------------------------------------------------------------------- $root_class
	protected ?Reflection_Class $root_class = null;

	//------------------------------------------------------------------------------ filterProperties
	/**
	 * Filter a list of properties by removing properties that should not be visible
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $source_properties Reflection\Reflection_Property[]
	 * @param $display_full_path boolean
	 * @return Reflection_Property[]
	 */
	protected function filterProperties(array $source_properties, bool $display_full_path = false)
		: array
	{
		$properties = [];
		/** @var $source_properties Reflection_Property[] */
		$source_properties = Replaces_Annotations::removeReplacedProperties($source_properties);
		foreach ($source_properties as $property_name => $source_property) {
			if (
				(!$this->composite_property || ($source_property->name !== $this->composite_property->name))
				&& (
					!$this->composite_link_property
					|| ($source_property->name !== $this->composite_link_property->name)
				)
				&& $source_property->isPublic()
				&& $source_property->isVisible(false, false)
				&& (($this->for === Feature::F_PRINT) || !Store::of($source_property)->isFalse())
			) {
				/** @noinspection PhpUnhandledExceptionInspection valid $property */
				$property = new Reflection_Property(
					$source_property->class, $source_property->path, $display_full_path ? 'path' : 'name'
				);
				$property->final_class      = $source_property->final_class;
				$property->link_class       = $this->root_class->name;
				$property->link_path        = $source_property->path;
				$property->root_class       = $source_property->root_class;
				$properties[$property_name] = $property;
			}
		}
		return $properties;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Get list of properties to display for a class
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Property[]|null[]
	 */
	public function getProperties(
		Reflection_Class $class, string $composite_class_name = null, bool $display_full_path = false
	) : array
	{
		if (isset($composite_class_name) && isA($class->name, Component::class)) {
			$composite_properties = call_user_func(
				[$class->name, 'getCompositeProperties'],
				$composite_class_name
			);
			$this->composite_property = reset($composite_properties) ?: null;
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
		$properties = $this->filterProperties($properties, $display_full_path);
		$this->sortProperties($properties);
		// TODO LOW reverse properties should come back, well displayed, and conditioned to the activation of an user feature
		/*
		$reverse_properties = $this->getReverseProperties($class);
		if ($reverse_properties) {
			$properties[] = null;
			$properties   = array_merge($properties, $reverse_properties);
		}
		*/
		return $properties;
	}

	//-------------------------------------------------------------------------- getReverseProperties
	/** @return Reflection_Property[] */
	protected function getReverseProperties(Reflection_Class $class) : array
	{
		// class and its parents
		$base_class     = Builder::current()->sourceClassName($class->name);
		$class_display  = Display::of($class)->value;
		$class_displays = Displays::of($class)->value;
		$class_name     = $class->name;
		$class_names    = [$class_name];
		while ($class_name && ($class_name !== $base_class)) {
			if ($class_name = get_parent_class($class_name)) {
				$class_names[] = $class_name;
			}
		}

		// dependencies : which properties point to the class ?
		$properties = Dependency::propertiesUsingClass($class_names);

		// filter and add properties
		$class_count          = [];
		$properties           = $this->filterProperties($properties);
		$property_class_names = [];
		foreach ($properties as $property_path => $property) {
			$property_class = $property->getFinalClass();
			if (
				Link_Annotation::of($property_class)->value
				|| Composite::of($property)?->value
				// TODO this consideration is only on some cases (here : lists can't deal with #Store(false))
				|| Store::of($property)->isFalse()
				|| !Class_\Store::of($property_class)->value
			) {
				unset($properties[$property_path]);
				continue;
			}
			if (isset($class_count[$property_class->name])) {
				$class_count[$property_class->name] ++;
			}
			else {
				$class_count[$property_class->name]
					= in_array($property->name, [$class_display, $class_displays]) ? 1 : 2;
			}
			$property->display = Loc::tr(Names::classToDisplay($property_class->name));
			$property_class_names[$property_path] = $property_class->name;
			$property->link_path = Builder::current()->sourceClassName($property_class)
				. '(' . $property->name . ')';
		}

		// properties display : only if multiple or different from #Display
		foreach ($properties as $property_path => $property) {
			if ($class_count[$property_class_names[$property_path]] > 1) {
				$property->display .= SP . '(' . Loc::tr($property->name) . ')';
			}
		}

		return $properties;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Property select controller, starting from a given root class
	 *
	 * @param $parameters Parameters
	 * - first shift : the root reference class name (ie a business object)
	 * - second shift : if set, the selected property path into the root reference class name
	 * - class_name : replace first if set : reference class name
	 * - property_path : replace second if set : property path (used for Reverse\Join\Class(property))
	 * @param $form  array   not used
	 * @param $files array[] not used
	 * @return ?string
	 * @throws ReflectionException
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$class_name = Set::elementClassNameOf(
			$parameters->getRawParameter('class_name') ?: $parameters->shiftUnnamed()
		);
		$this->for = $parameters->getRawParameter('for') ?: $parameters->shiftUnnamed();
		/** @noinspection PhpUnhandledExceptionInspection $class_name is always valid */
		$this->root_class = new Reflection_Class($class_name);
		if (List_::of($this->root_class)->lock) {
			return Loc::tr('You are not allowed to customize this list');
		}
		$property_path = $parameters->getRawParameter('property_path') ?: $parameters->shiftUnnamed();
		$top_property_class = (new Path($class_name, strval($property_path)))->toPropertyClass();
		if ($top_property_class instanceof Reflection_Class) {
			$properties = $this->getProperties($top_property_class);
		}
		elseif ($top_property_class->getType()->isClass()) {
			/** @noinspection PhpUnhandledExceptionInspection $top_property already tested */
			$properties = $this->getProperties(
				new Reflection_Class($top_property_class->getType()->getElementTypeAsString()),
				$top_property_class->final_class
			);
		}
		else {
			$properties = [];
		}
		if ($property_path) {
			foreach ($properties as $property) {
				if ($property) {
					$property->link_path = $property_path . DOT . $property->link_path;
				}
			}
			if (!$parameters->getRawParameter(Parameter::CONTAINER)) {
				$parameters->set(Parameter::CONTAINER, 'subtree');
			}
		}
		$objects = $parameters->getObjects();
		array_unshift(
			$objects,
			($top_property_class instanceof Reflection_Property) ? $top_property_class : null
		);
		$objects['class_name']         = Builder::current()->sourceClassName($class_name);
		$objects['link_class_feature'] = $this->for;
		$objects['properties']         = $properties;
		/**
		 * Objects for the view :
		 * first        Property the property object (with selected property name, or not)
		 * 'properties' Reflection_Property[] all properties from the reference class
		 */
		$all_expandable = Reflection_Property::$all_expandable;
		Reflection_Property::$all_expandable = true;
		$output = View::run($objects, $form, $files, Property::class, 'select');
		Reflection_Property::$all_expandable = $all_expandable;
		return $output;
	}

	//-------------------------------------------------------------------------------- sortProperties
	/** @param $properties Reflection_Property[] */
	protected function sortProperties(array &$properties) : void
	{
		uasort($properties, function(Reflection_Property $p1, Reflection_Property $p2) : int {
			return ($p2->getType()->isBasic() - $p1->getType()->isBasic())
				?: strcmp(Loc::tr(Names::propertyToDisplay($p1)), Loc::tr(Names::propertyToDisplay($p2)));
		});
	}

}
