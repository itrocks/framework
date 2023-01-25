<?php
namespace ITRocks\Framework\Mapper;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * A component is a class for objects that should not exist without their container object
 *
 * @store
 */
trait Component
{

	//------------------------------------------------------------------------- $composite_properties
	/**
	 * Composite property name
	 *
	 * Keys are :
	 * - the called class, as composite property name can be different for each class
	 * - the filter condition (a class or property name)
	 *
	 * @var array Reflection_Property[][]
	 */
	private static array $composite_properties;

	//--------------------------------------------------------------------------------------- dispose
	/**
	 * Default disposer call the remove
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name    object|string|null The composite class name or object
	 * @param $property_name string|null The composite property name
	 */
	public function dispose(object|string $class_name = null, string $property_name = null) : void
	{
		foreach (self::getCompositeProperties($class_name, $property_name) as $property) {
			/** @noinspection PhpUnhandledExceptionInspection $property from $this must be accessible */
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name    object|string|null The composite class name or object
	 * @param $property_name string|null The composite property name
	 * @return object
	 */
	public function getComposite(object|string $class_name = null, string $property_name = null)
		: object
	{
		/** @noinspection PhpUnhandledExceptionInspection property from $this must be accessible */
		return self::getCompositeProperty($class_name, $property_name)->getValue($this);
	}

	//------------------------------------------------------------------------ getCompositeProperties
	/**
	 * Get composite properties
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name    object|string|null The composite class name or object
	 * @param $property_name string|null The composite property name
	 * @return Reflection_Property[] key is the name of the property
	 */
	public static function getCompositeProperties(
		object|string $class_name = null, string $property_name = null
	) : array
	{
		// flexible parameters : first parameter can be a property name alone
		if (is_string($class_name) && !empty($class_name) && !isset($property_name)) {
			if (ctype_lower($class_name[0])) {
				$property_name = $class_name;
				$class_name    = null;
			}
		}
		elseif (is_object($class_name)) {
			$class_name = get_class($class_name);
		}
		$self = static::class;
		$path = $self . DOT . $class_name . DOT . $property_name;
		if (!isset(self::$composite_properties[$path])) {
			self::$composite_properties[$path] = [];
			/** @noinspection PhpUnhandledExceptionInspection self, and property that must be valid */
			$properties = empty($property_name)
				? (new Reflection_Class($self))->getAnnotedProperties('composite')
				: [new Reflection_Property($self, $property_name)];
			// take the right composite property
			foreach ($properties as $property) {
				$property_class = Builder::current()->sourceClassName($property->getType()->asString());
				if (!isset($class_name) || is_a($class_name, $property_class, true)) {
					self::$composite_properties[$path][$property->name] = $property;
				}
			}
			if (!self::$composite_properties[$path]) {
				// automatic composite property : filter all properties by class name as type
				/** @noinspection PhpUnhandledExceptionInspection self */
				foreach ((new Reflection_Class($self))->getProperties([T_EXTENDS, T_USE]) as $property) {
					$property_class = Builder::current()->sourceClassName($property->getType()->asString());
					if (!isset($class_name) || is_a($class_name, $property_class, true)) {
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
	 * @param $class_name    object|string|null The composite class name or object
	 * @param $property_name string|null The composite property name
	 * @return ?Reflection_Property
	 */
	public static function getCompositeProperty(
		object|string $class_name = null, string $property_name = null
	) : ?Reflection_Property
	{
		$properties = self::getCompositeProperties($class_name, $property_name);
		return reset($properties);
	}

	//---------------------------------------------------------------------------------- setComposite
	/**
	 * Sets composite object
	 *
	 * @param $object        object The composite object
	 * @param $property_name string|null The composite property name (needed if multiple)
	 */
	public function setComposite(object $object, string $property_name = null) : void
	{
		foreach (self::getCompositeProperties($object, $property_name) as $property) {
			$property->setValue($this, $object);
		}
	}

}
