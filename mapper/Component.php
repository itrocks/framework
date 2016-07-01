<?php
namespace SAF\Framework\Mapper;

use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;

/**
 * A component is a class for objects that should not exist without their container object
 *
 * @business
 */
trait Component
{

	//---------------------------------------------------------------------- $composite_properties
	/**
	 * Composite property name
	 *
	 * Keys are :
	 * - the called class, as composite property name can be different for each class
	 * - the filter condition (a class or property name)
	 *
	 * @var array Reflection_Property[][]
	 */
	private static $composite_properties;

	//--------------------------------------------------------------------------------------- dispose
	/**
	 * Default disposer call the remove
	 *
	 * @param $class_name    string|object The composite class name or object
	 * @param $property_name string The composite property name
	 */
	public function dispose($class_name = null, $property_name = null)
	{
		foreach (self::getCompositeProperties($class_name, $property_name) as $property) {
			$composite = $property->getValue($this);
			if (isset($composite)) {
				if (isA($composite, Remover::class)) {
					/** @var $composite Remover */
					$composite->remove($this);
				}
				else {
					Remover_Tool::removeObjectFromComposite($composite, $this);
				}
			}
		}
	}

	//---------------------------------------------------------------------------------- getComposite
	/**
	 * Gets composite object
	 *
	 * @param $class_name    string|object The composite class name or object
	 * @param $property_name string The composite property name
	 * @return object
	 */
	public function getComposite($class_name = null, $property_name = null)
	{
		return self::getCompositeProperty($class_name, $property_name)->getValue($this);
	}

	//------------------------------------------------------------------------ getCompositeProperties
	/**
	 * Get composite properties
	 *
	 * @param $class_name    string|object The composite class name or object
	 * @param $property_name string The composite property name
	 * @return Reflection_Property[] key is the name of the property
	 */
	public static function getCompositeProperties($class_name = null, $property_name = null)
	{
		// flexible parameters : first parameter can be a property name alone
		if (!isset($property_name) && is_string($class_name) && !empty($class_name)) {
			if (ctype_lower($class_name[0])) {
				$property_name = $class_name;
				$class_name = null;
			}
		}
		elseif (is_object($class_name)) {
			$class_name = get_class($class_name);
		}
		$self = get_called_class();
		$path = $self . DOT . $class_name . DOT . $property_name;
		if (!isset(self::$composite_properties[$path])) {
			self::$composite_properties[$path] = [];
			$properties = empty($property_name)
				? (new Reflection_Class($self))->getAnnotedProperties('composite')
				: [new Reflection_Property($self, $property_name)];
			// take the right composite property
			foreach ($properties as $property) {
				if (!isset($class_name) || is_a($class_name, $property->getType()->asString(), true)) {
					self::$composite_properties[$path][$property->name] = $property;
				}
			}
			if (!self::$composite_properties[$path]) {
				// automatic composite property : filter all properties by class name as type
				foreach (
					(new Reflection_Class($self))->getProperties([T_EXTENDS, T_USE]) as $property
				) {
					if (!isset($class_name) || is_a($class_name, $property->getType()->asString(), true)) {
						self::$composite_properties[$path][$property->name] = $property;
					}
				}
			}
		}
		return self::$composite_properties[$path];
	}

	//-------------------------------------------------------------------------- getCompositeProperty
	/**
	 * Gets composite property
	 *
	 * @param $class_name    string|object The composite class name or object
	 * @param $property_name string The composite property name
	 * @return Reflection_Property
	 */
	public static function getCompositeProperty($class_name = null, $property_name = null)
	{
		$properties = self::getCompositeProperties($class_name, $property_name);
		return reset($properties);
	}

	//---------------------------------------------------------------------------------- setComposite
	/**
	 * Sets composite object
	 *
	 * @param $object        object The composite object
	 * @param $property_name string The composite property name (needed if multiple)
	 */
	public function setComposite($object, $property_name = null)
	{
		foreach (self::getCompositeProperties($object, $property_name) as $property) {
			$property->setValue($this, $object);
		}
	}

}
