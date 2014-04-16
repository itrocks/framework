<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Builder;
use SAF\Framework\Reflection\Annotation\Template\Documented_Type_Annotation;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Tools\Names;

/**
 * The property name into the foreign class that contains current object
 *
 * This can return a virtual property name for link tables ! Check that the property exists before using it for a property access
 */
class Foreign_Annotation extends Documented_Type_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value               string
	 * @param $reflection_property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $reflection_property)
	{
		parent::__construct($value, $reflection_property);
		if (empty($this->value)) {
			$link = $reflection_property->getAnnotation('link')->value;
			$possibles = null;
			if ($link == 'Collection') {
				$possibles = $this->defaultCollection($reflection_property);
			}
			elseif ($link === 'Map') {
				$possibles = $this->defaultMap($reflection_property);
			}
			elseif ($link === 'Object') {
				$possibles = $this->defaultObject($reflection_property);
			}
			if (is_array($possibles) && count($possibles) == 1) {
				$this->value = reset($possibles);
			}
			elseif (count($possibles) > 1) {
				$class    = $reflection_property->class;
				$property = $reflection_property->name;
				$type     = $reflection_property->getType()->getElementTypeAsString();
				trigger_error(
					'Can\'t guess @foreign for ' . $class . '::' . $property . ' : '
					. 'please set @composite on one (and one only) ' . $type . ' property of type ' . $class
					. ' object, or force the ' . $class . '::' . $property . ' @foreign property name.',
					E_USER_ERROR
				);
			}
		}
	}

	//----------------------------------------------------------------------------- defaultCollection
	/**
	 * @param $reflection_property Reflection_Property
	 * @return string[]
	 */
	private function defaultCollection(Reflection_Property $reflection_property)
	{
		$type = $reflection_property->getType();
		$composites = [];
		$possibles = [];
		$foreign_class = new Reflection_Class(Builder::className($type->getElementTypeAsString()));
		foreach ($foreign_class->getAllProperties() as $foreign_property) {
			$foreign_type = $foreign_property->getType();
			if (
				$foreign_type->isClass()
				&& !$foreign_type->isMultiple()
				&& is_a($reflection_property->final_class, $foreign_type->asString(), true)
				&& ($foreign_property->getAnnotation('link')->value == 'Object')
			) {
				$possibles[] = $foreign_property->name;
				if ($foreign_property->getAnnotation('composite')->value) {
					$composites[] = $foreign_property->name;
				}
			}
		}
		return (count($composites) == 1)
			? $composites
			: $possibles;
	}

	//------------------------------------------------------------------------------------ defaultMap
	/**
	 * @param $reflection_property Reflection_Property
	 * @return string[]
	 */
	private function defaultMap(Reflection_Property $reflection_property)
	{
		$type = $reflection_property->getType();
		$possibles = [];
		$replace = [];
		$foreign_class = new Reflection_Class(Builder::className($type->getElementTypeAsString()));
		foreach ($foreign_class->getAllProperties() as $foreign_property) {
			$foreign_type = $foreign_property->getType();
			if (
				$foreign_type->isClass()
				&& $foreign_type->isMultiple()
				&& is_a($reflection_property->final_class, $foreign_type->getElementTypeAsString(), true)
				&& $foreign_property->getAnnotation('link')->value == 'Map'
				&& (
					$foreign_property->class != $reflection_property->class
					|| $foreign_property->name != $reflection_property->name
				)
			) {
				$possibles[$foreign_property->name] = $foreign_property->name;
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
				$reflection_property->getDeclaringClass()->getAnnotation('set')->value
			));
		}
		return $possibles;
	}

	//--------------------------------------------------------------------------------- defaultObject
	/**
	 * @param Reflection_Property $reflection_property
	 * @return string[]
	 */
	private function defaultObject(Reflection_Property $reflection_property)
	{
		$type = $reflection_property->getType();
		$possibles = [];
		$foreign_class = new Reflection_Class(Builder::className($type->asString()));
		foreach ($foreign_class->getAllProperties() as $foreign_property) {
			$foreign_type = $foreign_property->getType();
			if (
				$foreign_type->isClass()
				&& $foreign_type->isMultiple()
				&& $foreign_type->isInstanceOf($reflection_property->class)
				&& $foreign_property->getAnnotation('link')->value == 'Collection'
			) {
				$possibles[] = $foreign_property->name;
			}
		}
		return $possibles;
	}

}
