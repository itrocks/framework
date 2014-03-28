<?php
namespace SAF\Framework;

/**
 * Integrated properties toolbox, used to expand properties list when @integrated annotation is used
 */
abstract class Integrated_Properties
{

	//------------------------------------------------------------------------- expandUsingProperties
	/**
	 * Expands a list of properties using property or list of properties
	 *
	 * Only properties with an @integrated annotation will be used for extend
	 *
	 * @param $properties_list  Reflection_Property[]
	 * @param $using_properties Reflection_Property|Reflection_Property[]
	 * @param $object           object
	 * @return Reflection_Property[] added properties list (empty if none applies)
	 */
	public static function expandUsingProperties(
		&$properties_list, $using_properties, $object = null
	) {
		$expanded = [];
		if (!is_array($using_properties)) {
			$using_properties = [$using_properties];
		}
		/** @var $using_properties Reflection_Property[] */
		foreach ($using_properties as $property_name => $property) {
			$expanded = array_merge($expanded, self::expandUsingProperty(
				$properties_list, $property, $object, $property_name
			));
		}
		return $expanded;
	}

	//--------------------------------------------------------------------------- expandUsingProperty
	/**
	 * Expands a list of properties using a property
	 *
	 * Only properties with an @integrated annotation will be used for extend
	 *
	 * @param $properties_list Reflection_Property[] new indicies will be 'property.sub_property'
	 * @param $property        Reflection_Property
	 * @param $object          object
	 * @param $property_name   string
	 * @return Reflection_Property[] added properties list (empty if none applies) indices are 'property.sub_property'
	 */
	public static function expandUsingProperty(
		&$properties_list, $property, $object = null, $property_name = null
	) {
		if (empty($property_name) || is_numeric($property_name)) {
			$property_name = $property->name;
		}
		return self::expandUsingPropertyInternal($properties_list, $property, $object, $property_name);
	}

	//------------------------------------------------------------------- expandUsingPropertyInternal
	/**
	 * @param $properties_list Reflection_Property[] new indices will be 'property.sub_property'
	 * @param $property        Reflection_Property
	 * @param $object          object
	 * @param $property_name   string
	 * @param $display_prefix  string
	 * @param $blocks          string[]
	 * @return Reflection_Property[] added properties list (empty if none applies) indices are
	 *         'property.sub_property'
	 * @todo probably things to clean up (was patched for 'all properties as values' without controls)
	 */
	private static function expandUsingPropertyInternal(
		&$properties_list, $property, $object, $property_name, $display_prefix = '', $blocks = []
	) {
		$expanded = [];
		/** @var $integrated Integrated_Annotation */
		$integrated = $property->getListAnnotation('integrated');
		if ($integrated->value && !$property->isStatic()) {
			if ($integrated->has('block')) {
				$blocks[$property->path ?: $property->name] = $property->path ?: $property->name;
			}
			$integrated_simple = $integrated->has('simple');
			$sub_properties_class = $property->getType()->asReflectionClass();
			$expand_properties = $sub_properties_class->getAllProperties();
			$value = $property->getValue($object) ?: Builder::create($property->getType()->asString());
			foreach ($expand_properties as $sub_property_name => $sub_property) {
				if (!$sub_property->getListAnnotation('user')->has('invisible')) {
					$display = ($display_prefix . ($display_prefix ? DOT : '')
						. $property->name . DOT . $sub_property_name);
					$sub_prefix = $integrated_simple ? $display_prefix : $display;
					if ($more_expanded = self::expandUsingPropertyInternal(
						$properties_list, $sub_property, $value, $property_name . DOT . $sub_property_name,
						$sub_prefix, $blocks
					)) {
						$expanded = array_merge($expanded, $more_expanded);
					}
					else {
						$sub_property = new Reflection_Property_Value(
							$sub_property->class, $sub_property->name, $value, false, true
						);
						$sub_property->final_class = $sub_properties_class->name;
						$sub_property->display = $integrated_simple
							? (
								$integrated->has('alias')
								? $sub_property->getAnnotation('alias')->value
								: $sub_property_name
							)
							: $display;
						/** @var $block_annotation List_Annotation */
						$block_annotation = $sub_property->setAnnotationLocal('block');
						foreach ($blocks as $block) {
							$block_annotation->add($block);
						}
						$sub_property->path = $property_name . DOT . $sub_property_name;
						$properties_list[$property_name . DOT . $sub_property_name] = $sub_property;
						$expanded[$property_name . DOT . $sub_property_name] = $sub_property;
					}
				}
			}
		}
		return $expanded;
	}

}
