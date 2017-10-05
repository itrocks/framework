<?php
namespace ITRocks\Framework\Reflection;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Reflection\Annotation\Property\Alias_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Integrated_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;

/**
 * Integrated properties toolbox, used to expand properties list when @integrated annotation is used
 */
class Integrated_Properties
{

	//--------------------------------------------------------------------------------------- $object
	/**
	 * Referent root object
	 *
	 * @var object
	 */
	protected $object;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $object object referent root object is optional : can be used for each call to
	 *                       expandUsingProperty
	 */
	public function __construct($object = null)
	{
		if (isset($object)) {
			$this->object = $object;
		}
	}

	//---------------------------------------------------------------------------------- defaultValue
	/**
	 * Get property value, with default
	 *
	 * @param $property Reflection_Property
	 * @return object
	 */
	protected function defaultValue(Reflection_Property $property)
	{
		// force creation of a default object value for the property, if empty
		if (!($value = $property->getValue($this->object))) {
			$value = Builder::create($property->getType()->asString());
			if ($property->getAnnotation('component')->value && isA($value, Component::class)) {
				// TODO HIGHEST The composite is not $this->object, but the $value's parent. Test needed
				/** @var $value Component */
				$value->setComposite($this->object);
			}
		}
		return $value;
	}

	//--------------------------------------------------------------------------- expandUsingProperty
	/**
	 * Expands a list of properties using a property
	 *
	 * Only properties with an @integrated annotation will be used for extend
	 *
	 * @param $properties_list Reflection_Property[] new indices will be 'property.sub_property'
	 * @param $property        Reflection_Property
	 * @param $object          object if set, replaces the referent root object for all next calls
	 * @return Reflection_Property[] added properties list (empty if none applies) keys are
	 *         'property.sub_property'
	 */
	public function expandUsingProperty(array &$properties_list, $property, $object = null)
	{
		if (isset($object)) {
			$this->object = $object;
		}
		return self::expandUsingPropertyInternal($properties_list, $property);
	}

	//------------------------------------------------------------------- expandUsingPropertyInternal
	/**
	 * @param $properties_list Reflection_Property[] new keys will be 'property.sub_property'
	 * @param $property        Reflection_Property
	 * @param $display_prefix  string
	 * @param $blocks          string[]
	 * @return Reflection_Property_Value[] added properties list (empty if none applies) keys are
	 *         'property.sub_property'
	 */
	protected function expandUsingPropertyInternal(
		array &$properties_list, $property, $display_prefix = '', array $blocks = []
	) {
		$expanded      = [];
		$integrated    = Integrated_Annotation::of($property);
		$property_path = $property->path ?: $property->name;
		if (
			$integrated->value
			&& !$property->isStatic()
			&& (!$integrated->has(Integrated_Annotation::FINAL_) || !strpos($property_path, DOT))
		) {
			if ($integrated->has(Integrated_Annotation::BLOCK)) {
				$blocks[$property_path] = $property_path;
			}
			$expand_properties = $integrated->properties
				? $this->getExplicitIntegratedProperties($property, $integrated)
				: $this->getImplicitIntegratedProperties($property);
			$this->startFromRootClass($expand_properties, $property);
			$this->defaultValue($property);
			$expanded = $this->prepareExpandedProperties(
				$properties_list, $display_prefix, $blocks, $expand_properties, $property_path, $integrated
			);
		}

		return $expanded;
	}

	//--------------------------------------------------------------- getExplicitIntegratedProperties
	/**
	 * @param $property Reflection_Property
	 * @param $integrated Integrated_Annotation
	 * @return Reflection_Property[]
	 */
	protected function getExplicitIntegratedProperties(
		Reflection_Property $property, Integrated_Annotation $integrated
	) {
		$expand_properties    = [];
		$property_path        = $property->path ?: $property->name;
		$sub_properties_class = $property->getType()->asReflectionClass();
		foreach ($integrated->properties as $integrated_property_path) {
			$expand_properties[$property_path . DOT . $integrated_property_path]
				= new Reflection_Property($sub_properties_class->name, $integrated_property_path);
		}
		return $expand_properties;
	}

	//--------------------------------------------------------------- getImplicitIntegratedProperties
	/**
	 * Implicit integrated properties : take from the property type (class) properties list
	 *
	 * @param Reflection_Property $property
	 * @return Reflection_Property[]
	 */
	protected function getImplicitIntegratedProperties(Reflection_Property $property)
	{
		$expand_properties    = [];
		$property_path        = $property->path ?: $property->name;
		$sub_properties_class = $property->getType()->asReflectionClass();
		foreach (
			$sub_properties_class->getProperties([T_EXTENDS, T_USE, Reflection_Class::T_SORT])
			as $sub_property_name => $sub_property
		) {
			if (
				!$sub_property->isStatic()
				&& !$sub_property->getListAnnotation(User_Annotation::ANNOTATION)->has(
					User_Annotation::INVISIBLE
				)
				&& (
					!$property->getAnnotation('component')->value
					|| !$sub_property->getAnnotation('composite')->value
				)
			) {
				$expand_properties[$property_path . DOT . $sub_property_name] = $sub_property;
			}
		}
		return $expand_properties;
	}

	//--------------------------------------------------------------------- prepareExpandedProperties
	/**
	 * @param $properties_list   Reflection_Property[]
	 * @param $display_prefix    string
	 * @param $blocks            string[]
	 * @param $expand_properties Reflection_Property[]
	 * @param $property_path     string
	 * @param $integrated        Integrated_Annotation
	 * @return Reflection_Property_Value[]
	 */
	protected function prepareExpandedProperties(
		array &$properties_list, $display_prefix, array $blocks, $expand_properties, $property_path,
		Integrated_Annotation $integrated
	) {
		$expanded          = [];
		$integrated_alias  = $integrated->has(Integrated_Annotation::ALIAS);
		$integrated_simple = $integrated->has(Integrated_Annotation::SIMPLE);
		foreach ($expand_properties as $sub_property_path => $sub_property) {
			// prefixed display, sub-prefix
			$display = $display_prefix . ($display_prefix ? DOT : '') . $property_path
				. DOT . $sub_property_path;
			$sub_prefix = $integrated_simple ? $display_prefix : $display;
			// recurse
			if ($more_expanded = self::expandUsingPropertyInternal(
				$properties_list, $sub_property, $sub_prefix, $blocks
			)) {
				$expanded = array_merge($expanded, $more_expanded);
			}
			// if no recurse : prepare and add expanded property
			else {
				$sub_property = $this->prepareExpandedProperty(
					$blocks, $integrated_alias, $integrated_simple, $sub_property, $sub_property_path,
					$display
				);
				$properties_list[$sub_property_path] = $sub_property;
				$expanded[$sub_property_path]        = $sub_property;
			}
		}
		return $expanded;
	}

	//----------------------------------------------------------------------- prepareExpandedProperty
	/**
	 * @param $blocks            string[]
	 * @param $integrated_alias  boolean
	 * @param $integrated_simple boolean
	 * @param $sub_property      Reflection_Property
	 * @param $sub_property_path string
	 * @param $display           string
	 * @return Reflection_Property_Value
	 */
	protected function prepareExpandedProperty(
		array $blocks, $integrated_alias, $integrated_simple, $sub_property,
		$sub_property_path, $display
	) {
		$sub_property = new Reflection_Property_Value(
			$sub_property->root_class, $sub_property_path, $this->object, false, true
		);
		$sub_property->display = Loc::tr(
			$integrated_simple
				? (
					$integrated_alias
					? $sub_property->getAnnotation(Alias_Annotation::ANNOTATION)->value
					: rLastParse($sub_property_path, DOT, 1, true)
				)
				: $display
		);
		// add property to all parent blocks
		/** @var $block_annotation List_Annotation */
		$block_annotation = $sub_property->setAnnotationLocal(Annotation::BLOCK);
		foreach ($blocks as $block) {
			$block_annotation->add($block);
		}
		return $sub_property;
	}

	//---------------------------------------------------------------------------- startFromRootClass
	/**
	 * Properties must all start from the same root class
	 *
	 * @param $properties Reflection_Property[]
	 * @param $property   Reflection_Property
	 */
	protected function startFromRootClass(array &$properties, Reflection_Property $property)
	{
		foreach ($properties as $expand_property_path => $expand_property) {
			$expand_property->aliased_path = ($property->aliased_path ?: $property->alias)
				. DOT . $expand_property->aliased_path;
			$expand_property->path       = $expand_property_path;
			$expand_property->root_class = $property->root_class ?: $property->final_class;
		}
	}

}
