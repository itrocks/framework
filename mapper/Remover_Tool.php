<?php
namespace ITRocks\Framework\Mapper;

use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * The remover tool enables common process of removal of composite objects from a container object
 */
abstract class Remover_Tool
{

	//--------------------------------------------------------------------- removeObjectFromComposite
	/**
	 * Default remover removes an object from all collections properties of the object
	 *
	 * @param $composite object The object that contains the given object
	 * @param $object    object contained object to remove
	 * @return integer removed instances count
	 */
	public static function removeObjectFromComposite($composite, $object)
	{
		$count = 0;
		/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
		foreach ((new Reflection_Class(get_class($composite)))->accessProperties() as $property) {
			$type = $property->getType();
			if ($type->isClass() && isA($object, $type->getElementTypeAsString())) {
				$property_name = $property->name;
				if ($type->isMultiple()) {
					$remover = $property->getAnnotation('remover');
					if ($remover->value) {
						$count += call_user_func([$composite, $remover->value], $object);
					}
					else {
						$property_value =& $composite->$property_name;
						foreach ($property_value as $key => $value) {
							if ($value === $object) {
								unset($property_value[$key]);
								$count ++;
							}
						}
					}
				}
				elseif ($property->getValue($composite) === $object) {
					unset($composite->$property_name);
					$count ++;
				}
			}
		}
		return $count;
	}

}
