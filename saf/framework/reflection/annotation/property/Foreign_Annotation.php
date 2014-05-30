<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Builder;
use SAF\Framework\PHP;
use SAF\Framework\Reflection;
use SAF\Framework\Reflection\Annotation\Annoted;
use SAF\Framework\Reflection\Annotation\Template\Documented_Type_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use SAF\Framework\Reflection\Interfaces\Reflection_Class;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;
use SAF\Framework\Tools\Names;

/**
 * The property name into the foreign class that contains current object
 *
 * This can return a virtual property name for link tables ! Check that the property exists before
 * using it for a property access
 */
class Foreign_Annotation extends Documented_Type_Annotation implements Property_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $property)
	{
		parent::__construct($value, $property);
		if (empty($this->value)) {
			$link = $property->getAnnotation('link')->value;
			$possibles = null;
			if ($link === Link_Annotation::COLLECTION) {
				$possibles = $this->defaultCollection($property);
			}
			elseif ($link === Link_Annotation::MAP) {
				$possibles = $this->defaultMap($property);
			}
			elseif ($link === Link_Annotation::OBJECT) {
				$possibles = $this->defaultObject($property);
			}
			if (is_array($possibles) && count($possibles) == 1) {
				$this->value = reset($possibles);
			}
			elseif (count($possibles) > 1) {
				$class_name    = $property->getDeclaringClassName();
				$property_name = $property->getName();
				$type_name     = $property->getType()->getElementTypeAsString();
				trigger_error(
					'Can\'t guess @foreign for ' . $class_name . '::' . $property_name . ' : '
					. 'please set @composite on one (and one only) ' . $type_name . ' property of type '
					. $class_name . ' object, or force the ' . $class_name . '::' . $property_name
					. ' @foreign property name.',
					E_USER_ERROR
				);
			}
		}
	}

	//----------------------------------------------------------------------------- defaultCollection
	/**
	 * @param $property Reflection_Property
	 * @return string[]
	 */
	private function defaultCollection(Reflection_Property $property)
	{
		$composites = [];
		$possibles = [];
		$foreign_class = $this->getForeignClass($property);
		$final_class_name = $property->getFinalClassName();
		foreach ($foreign_class->getProperties([T_EXTENDS, T_USE]) as $foreign_property) {
			$foreign_type = $foreign_property->getType();
			if (
				$foreign_type->isClass()
				&& !$foreign_type->isMultiple()
				&& is_a($final_class_name, $foreign_type->asString(), true)
				&& ($foreign_property->getAnnotation('link')->value == Link_Annotation::OBJECT)
			) {
				$possibles[] = $foreign_property->getName();
				if ($foreign_property->getAnnotation('composite')->value) {
					$composites[] = $foreign_property->getName();
				}
			}
		}
		return (count($composites) == 1) ? $composites : $possibles;
	}

	//------------------------------------------------------------------------------------ defaultMap
	/**
	 * @param $property Reflection_Property
	 * @return string[]
	 */
	private function defaultMap(Reflection_Property $property)
	{
		$possibles = [];
		$replace = [];
		$foreign_class = $this->getForeignClass($property);
		foreach ($foreign_class->getProperties([T_EXTENDS, T_USE]) as $foreign_property) {
			$foreign_type = $foreign_property->getType();
			if (
				$foreign_type->isClass()
				&& $foreign_type->isMultiple()
				&& is_a(
					$property->getFinalClassName(), $foreign_type->getElementTypeAsString(), true
				)
				&& $foreign_property->getAnnotation('link')->value == Link_Annotation::MAP
				&& (
					($foreign_property->getDeclaringClassName() != $property->getDeclaringClassName())
					|| ($foreign_property->getName() != $property->getName())
				)
			) {
				$possibles[$foreign_property->getName()] = $foreign_property->getName();
				$replaced= $foreign_property->getAnnotation('replaces')->value;
				if ($replaced) {
					$replace[] = $replaced;
				}
			}
		}
		foreach ($replace as $replaced) {
			if (isset($possibles[$replaced])) {
				unset($possibles[$replaced]);
			}
		}
		if (count($possibles) != 1) {
			$this->value = Names::classToProperty(Names::setToClass(
					$property->getDeclaringClass()->getAnnotation('set')->value
			));
		}
		return $possibles;
	}

	//--------------------------------------------------------------------------------- defaultObject
	/**
	 * @param $property Reflection_Property
	 * @return string[]
	 */
	private function defaultObject(Reflection_Property $property)
	{
		$possibles = [];
		$foreign_class = $this->getForeignClass($property);
		foreach ($foreign_class->getProperties([T_EXTENDS, T_USE]) as $foreign_property) {
			$foreign_type = $foreign_property->getType();
			if (
				$foreign_type->isClass()
				&& $foreign_type->isMultiple()
				&& $foreign_type->isInstanceOf($property->getDeclaringClass())
				&& $foreign_property->getAnnotation('link')->value == Link_Annotation::COLLECTION
			) {
				$possibles[] = $foreign_property->getName();
			}
		}
		return $possibles;
	}

	//------------------------------------------------------------------------------- getForeignClass
	/**
	 * @param $property Reflection_Property
	 * @return Reflection_Class
	 */
	private function getForeignClass(Reflection_Property $property)
	{
		$type = $property->getType();
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

}
