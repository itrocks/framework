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
	 * @param $filter string|object The composite class name or object, or the composite property name
	 */
	public function dispose($filter = null)
	{
		foreach (self::getCompositeProperties($filter) as $property) {
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
	 * @param $filter string|object The composite class name or object, or the composite property name
	 * @return object
	 */
	public function getComposite($filter = null)
	{
		$properties = self::getCompositeProperties($filter);
		$property_name = reset($properties)->name;
		return $this->$property_name;
	}

	//------------------------------------------------------------------------ getCompositeProperties
	/**
	 * Get composite properties
	 *
	 * @param $filter string|object The composite class name or object, or the composite property name
	 * @return Reflection_Property[]
	 */
	public static function getCompositeProperties($filter = null)
	{
		if (is_object($filter)) {
			$filter_is_property = false;
			$filter = get_class($filter);
		}
		else {
			$filter_is_property = ($filter[0] >= 'a') || ($filter[0] <= 'z') || ($filter[0] == '_');
		}
		$class = get_called_class();
		if (!isset(self::$composite_property_name[$class][$filter])) {
			$properties = Reflection_Class::getInstanceOf($class)->getAnnotedProperties("composite");
			foreach ($properties as $property) {
				if (
					empty($filter)
					|| ($filter_is_property && ($property->getAnnotation("foreign") == $filter))
					|| (!$filter_is_property && is_subclass_of($class, $filter))
				) {
					$composite_property_name[$class][$filter][$property->name] = $property;
				}
			}
		}
		return self::$composite_property_name[$class][$filter];
	}

	//---------------------------------------------------------------------------------- setComposite
	/**
	 * Sets composite object
	 *
	 * @param $object object The composite object
	 * @param $filter string|null If set, the name of the foreign property into composite (needed if multiple)
	 */
	public function setComposite($object, $filter = null)
	{
		if (!isset($filter)) {
			$filter = $object;
		}
		foreach (self::getCompositeProperties($filter) as $property) {
			$property_name = $property->name;
			$this->$property_name = $object;
		}
	}

}
