<?php
namespace SAF\Framework;

abstract class Remover_Tool
{

	//--------------------------------------------------------------------- removeObjectFromComopsite
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
		$class = Reflection_Class::getInstanceOf($composite);
		foreach ($class->accessProperties() as $property) {
			$type = $property->getType();
			if ($type->isClass() && is_subclass_of($object, $type->getElementTypeAsString())) {
				$property_name = $property->name;
				if ($type->isMultiple()) {
					$remover = $property->getAnnotation("remover");
					if ($remover->value) {
						$count += call_user_func(array($composite, $remover->value), $object);
					}
					else {
						foreach ($composite->$property_name as $key => $value) {
							if ($value === $object) {
								unset($composite->$property_name[$key]);
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
		$class->accessPropertiesDone();
		return $count;
	}

}
