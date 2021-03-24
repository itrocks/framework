<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Builder;
use ITRocks\Framework\PHP;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Annotation\Template\Documented_Type_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Tools\Names;
use ReflectionException;

/**
 * The property name into the foreign class that contains current object
 *
 * This can return a virtual property name for link tables ! Check that the property exists before
 * using it for a property access
 */
class Foreign_Annotation extends Documented_Type_Annotation implements Property_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'foreign';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Reflection_Property
	 * @throws ReflectionException
	 */
	public function __construct($value, Reflection_Property $property)
	{
		parent::__construct($value);
		if (empty($this->value)) {
			$link = Link_Annotation::of($property);
			if ($link->isCollection()) {
				$possibles = $this->defaultCollection($property);
			}
			elseif ($link->isMap()) {
				$possibles = $this->defaultMap($property);
			}
			elseif ($link->isObject()) {
				$possibles = $this->defaultObject($property);
			}
			else {
				$possibles = [];
			}
			if (count($possibles) === 1) {
				$this->value = reset($possibles);
			}
			elseif (count($possibles) > 1) {
				$class_name    = $property->getDeclaringClassName();
				$property_name = $property->getName();
				$type_name     = $property->getType()->getElementTypeAsString();
				trigger_error(
					"Can't guess @foreign for " . $class_name . '::' . $property_name . ' : '
					. 'please set @composite on one (and one only) ' . $type_name . ' property of type '
					. $class_name . ' object, or force the ' . $class_name . '::' . $property_name
					. ' @foreign property name. Possibles properties are ' . join(', ', $possibles),
					E_USER_WARNING
				);
				$this->value = reset($possibles);
			}
		}
	}

	//----------------------------------------------------------------------------- defaultCollection
	/**
	 * @param $property Reflection_Property
	 * @return string[] Possibles properties names
	 * @throws ReflectionException
	 */
	private function defaultCollection(Reflection_Property $property)
	{
		$possibles        = [];
		$foreign_class    = $this->getForeignClass($property);
		$final_class_name = $property->getFinalClassName();
		foreach ($foreign_class->getProperties([T_EXTENDS, T_USE]) as $foreign_property) {
			$foreign_type = $foreign_property->getType();
			if (
				$foreign_type->isClass()
				&& !$foreign_type->isMultiple()
				&& is_a($final_class_name, $foreign_type->asString(), true)
				&& Link_Annotation::of($foreign_property)->isObject()
			) {
				$possibles[$foreign_property->getName()] = $foreign_property;
			}
		}
		$possibles = Replaces_Annotations::removeReplacedProperties($possibles);
		$possibles = $this->reduceToComposites($possibles);
		return array_keys($possibles);
	}

	//------------------------------------------------------------------------------------ defaultMap
	/**
	 * @param $property Reflection_Property
	 * @return string[] Possibles properties names
	 * @throws ReflectionException
	 */
	private function defaultMap(Reflection_Property $property)
	{
		$possibles = [];
		$foreign_class = $this->getForeignClass($property);
		foreach ($foreign_class->getProperties([T_EXTENDS, T_USE]) as $foreign_property) {
			$foreign_type = $foreign_property->getType();
			if (
				$foreign_type->isClass()
				&& $foreign_type->isMultiple()
				&& is_a(
					$property->getFinalClassName(), $foreign_type->getElementTypeAsString(), true
				)
				&& Link_Annotation::of($foreign_property)->isMap()
				&& (
					($foreign_property->getDeclaringClassName() != $property->getDeclaringClassName())
					|| ($foreign_property->getName() != $property->getName())
				)
			) {
				$possibles[$foreign_property->getName()] = $foreign_property;
			}
		}
		$possibles = Replaces_Annotations::removeReplacedProperties($possibles);
		$possibles = $this->reduceToComposites($possibles);
		if (count($possibles) != 1) {
			$this->value = Names::classToProperty($property->getDeclaringClassName());
		}
		return array_keys($possibles);
	}

	//--------------------------------------------------------------------------------- defaultObject
	/**
	 * @param $property Reflection_Property
	 * @return string[] Possibles properties names
	 * @throws ReflectionException
	 */
	private function defaultObject(Reflection_Property $property)
	{
		$possibles     = [];
		$foreign_class = $this->getForeignClass($property);
		foreach ($foreign_class->getProperties([T_EXTENDS, T_USE]) as $foreign_property) {
			$foreign_type = $foreign_property->getType();
			if (
				$foreign_type->isClass()
				&& $foreign_type->isInstanceOf($property->getDeclaringClass())
				&& (
					$property->getAnnotation('component')->value
					|| ($foreign_type->isMultiple() && Link_Annotation::of($foreign_property)->isCollection())
				)
			) {
				$possibles[$foreign_property->getName()] = $foreign_property;
			}
		}
		$properties = Replaces_Annotations::removeReplacedProperties($possibles);
		$properties = $this->reduceToComposites($properties);
		return array_keys($properties);
	}

	//------------------------------------------------------------------------------- getForeignClass
	/**
	 * @param $property Reflection_Property
	 * @return Reflection_Class
	 * @throws ReflectionException
	 */
	private function getForeignClass(Reflection_Property $property)
	{
		$type               = $property->getType();
		$foreign_class_name = Builder::className($type->getElementTypeAsString());
		if ($property instanceof PHP\Reflection_Property) {
			$foreign_class = PHP\Reflection_Class::of($foreign_class_name);
		}
		else {
			$reflection_class = new Reflection\Reflection_Class(
				get_class($property->getDeclaringClass())
			);
			$foreign_class = $reflection_class->newInstance($foreign_class_name);
		}
		return $foreign_class;
	}

	//---------------------------------------------------------------------------- reduceToComposites
	/**
	 * If multiple properties (more than 1) and one (or more) of them are @composite, reduce the list
	 * to the @composite properties. Else returns $properties without any change.
	 *
	 * @param $properties Reflection_Property[]
	 * @return Reflection_Property[]
	 */
	private function reduceToComposites(array $properties)
	{
		if (count($properties) > 1) {
			$composite_properties = [];
			foreach ($properties as $property) {
				if ($property->getAnnotation('composite')->value) {
					$composite_properties[$property->getName()] = $property;
				}
			}
			if ($composite_properties) {
				$properties = $composite_properties;
			}
		}
		return $properties;
	}

}
