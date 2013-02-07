<?php
namespace SAF\Framework;

trait Component
{

	//------------------------------------------------------------------------- $parent_property_name
	/**
	 * Parent property name
	 * Indice is the called class, as parent property name can be different for each class
	 *
	 * @var string[]
	 */
	private static $parent_property_name;

	//--------------------------------------------------------------------------------------- dispose
	/**
	 * Default disposer call the remove
	 *
	 * @return object
	 */
	public function dispose()
	{
		$parent = $this->getParent();
		if (is_subclass_of($parent, 'SAF\Framework\Component_Remover')) {
			$parent->removeComponent($this);
		}
	}

	//------------------------------------------------------------------------------------- getParent
	/**
	 * Gets parent object
	 *
	 * @return object
	 */
	public function getParent()
	{
		$property_name = $this->getParentPropertyName();
		return $this->$property_name;
	}

	//------------------------------------------------------------------------- getParentPropertyName
	/**
	 * Get parent property name
	 *
	 * @return string
	 */
	public static function getParentPropertyName()
	{
		$class = get_called_class();
		if (!isset(self::$parent_property_name[$class])) {
			// reverse because child class parent property must be used instead of its parent one
			foreach (
				array_reverse(Reflection_Class::getInstanceOf($class)->getAllProperties()) as $property
			) {
				$parent = $property->getAnnotation("parent");
				if ($parent->value) {
					self::$parent_property_name[$class] = $property->name;
					break;
				}
			}
		}
		return self::$parent_property_name[$class];
	}

	//------------------------------------------------------------------------------------- setParent
	/**
	 * Sets parent object
	 *
	 * @param $object object
	 */
	public function setParent($object)
	{
		$property_name = $this->getParentPropertyName();
		$this->$property_name = $object;
	}

}
