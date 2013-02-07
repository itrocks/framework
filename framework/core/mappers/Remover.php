<?php
namespace SAF\Framework;

trait Remover
{

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Default remover removes an object from all collections properties of the object
	 *
	 * @param $object object contained object to remove
	 * @return integer removed instances count
	 */
	public function remove($object)
	{
		$count = 0;
		$class = Reflection_Class::getInstanceOf($this);
		foreach ($class->accessProperties() as $property) {
			$type = $property->getType();
			if ($type->isClass() && is_subclass_of($object, $type->getElementTypeAsString())) {
				$property_name = $property->name;
				if ($type->isMultiple()) {
					$remover = $property->getAnnotation("remover");
					if ($remover->value) {
						$count += call_user_func(array($this, $remover->value), $object);
					}
					else {
						foreach ($this->$property_name as $key => $value) {
							if ($value === $object) {
								unset($this->$property_name[$key]);
								$count ++;
							}
						}
					}
				}
				else {
					unset($this->$property_name);
					$count ++;
				}
			}
		}
		$class->accessPropertiesDone();
		return $count;
	}

}
