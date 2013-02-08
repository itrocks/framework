<?php
namespace SAF\Framework;

trait Component
{

	//---------------------------------------------------------------------- $composite_property_name
	/**
	 * Composite property name
	 *
	 * Indices are :
	 * - the called class, as composite property name can be different for each class
	 * - the filter condition (a class or property name)
	 *
	 * @var string[]
	 */
	private static $composite_property_name;

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
				if ((new Type(get_class($composite)))->usesTrait('SAF\Framework\Remover')) {
					/** @var $composite Remover */
					$composite->remove($this);
				}
				else Remover_Tool::removeObjectFromComposite($composite, $this);
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
		$properties = self::getCompositeProperties($class_name, $property_name);
		$property_name = reset($properties)->name;
		return $this->$property_name;
	}

	//------------------------------------------------------------------------ getCompositeProperties
	/**
	 * Get composite properties
	 *
	 * @param $class_name    string|object The composite class name or object
	 * @param $property_name string The composite property name
	 * @return Reflection_Property[]
	 */
	public static function getCompositeProperties($class_name = null, $property_name = null)
	{
		// flexible parameters : first parameter can be a property name alone
		if (!isset($property_name) && is_string($class_name) && !empty($class_name)) {
			if ($class_name[0] >= 'a' && $class_name[0] <= 'z') {
				$property_name = $class_name;
				$class_name = null;
			}
		}
		elseif (is_object($class_name)) {
			$class_name = get_class($class_name);
		}
		$self = get_called_class();
		$path = $self . "." . $class_name . "." . $property_name;
		if (!isset(self::$composite_property_name[$path])) {
			self::$composite_property_name[$path] = array();
			$properties = empty($property_name) ?
				Reflection_Class::getInstanceOf($self)->getAnnotedProperties("composite")
				: array(Reflection_Property::getInstanceOf($self, $property_name));
			foreach ($properties as $property) {
				if (!isset($class_name) || $property->getType()->isInstanceOf($class_name)) {
					self::$composite_property_name[$path][$property->name] = $property;
				}
			}
		}
		return self::$composite_property_name[$path];
	}

	//---------------------------------------------------------------------------------- setComposite
	/**
	 * Sets composite object
	 *
	 * @param $object        object The composite object
	 * @param $property_name string The composite property name (needed if multiple
	 */
	public function setComposite($object, $property_name = null)
	{
		foreach (self::getCompositeProperties($object, $property_name) as $property) {
			$name = $property->name;
			$this->$name = $object;
		}
	}

}
