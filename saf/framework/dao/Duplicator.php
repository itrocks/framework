<?php
namespace SAF\Framework\Dao;

use SAF\Framework\Dao;
use SAF\Framework\Dao\Data_Link\Identifier_Map;
use SAF\Framework\Mapper\Component;
use SAF\Framework\Reflection\Annotation\Property\Link_Annotation;
use SAF\Framework\Reflection\Reflection_Class;

/**
 * This process class prepares business object linked to storage for duplicate
 *
 * It removes all Data link identifiers, where is needed.
 */
class Duplicator
{

	//------------------------------------------------------------------------------------------ $dao
	/**
	 * @var Identifier_Map
	 */
	private $dao;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * The constructor can be called with a specific data link.
	 * The default data link will be Dao::current()
	 * This works only for Identifier_Map data links
	 *
	 * @param $dao Identifier_Map
	 */
	public function __construct(Identifier_Map $dao = null)
	{
		$this->dao = isset($dao) ? $dao : Dao::current();
	}

	//------------------------------------------------------------------------------- createDuplicate
	/**
	 * @param $object object
	 */
	public function createDuplicate($object)
	{
		if ($this->dao->getObjectIdentifier($object)) {
			// duplicate @link Collection and Map properties values
			$class_name = get_class($object);
			$class = new Reflection_Class($class_name);
			/** @var $link Link_Annotation */
			$link = $class->getAnnotation('link');
			$exclude_properties = $link->value
				? array_keys((new Reflection_Class($link->value))->getProperties([T_EXTENDS, T_USE]))
				: [];
			foreach ($class->accessProperties() as $property) {
				if (!$property->isStatic() && !in_array($property->name, $exclude_properties)) {
					$property_link = $property->getAnnotation('link')->value;
					// @link Collection : must disconnect objects
					// @link Collection | Map : duplicate and remove reference to the parent id
					if (in_array($property_link, [Link_Annotation::COLLECTION, Link_Annotation::MAP])) {
						$elements = $property->getValue($object);
						if ($property_link == Link_Annotation::COLLECTION) {
							foreach ($elements as $element) {
								$this->createDuplicate($element);
							}
						}
						$this->removeCompositeFromComponents($elements, $class_name);
					}
				}
			}
			// duplicate object
			$this->dao->disconnect($object);
			// after duplicate
			$this->onDuplicate($object, $class);
		}
	}

	//----------------------------------------------------------------------------------- onDuplicate
	/**
	 * Call onDuplicate methods defined by @duplicate class annotation
	 *
	 * @param $object object
	 * @param $class  Reflection_Class
	 */
	private function onDuplicate($object, Reflection_Class $class)
	{
		foreach ($class->getAnnotations('duplicate') as $on_duplicate) {
			$callback = explode('::', $on_duplicate->value);
			if (($callback[1] === true) || is_numeric($callback[1])) {
				$callback[1] = 'onDuplicate';
			}
			if (isA($object, $callback[0])) {
				call_user_func([$object, $callback[1]]);
			}
			else {
				call_user_func([$callback[0], $callback[1]], $object);
			}
		}
	}

	//----------------------------------------------------------------- removeCompositeFromComponents
	/**
	 * @param $elements             object[]|Component[] the component objects
	 * @param $composite_class_name string the composite class name
	 */
	private function removeCompositeFromComponents($elements, $composite_class_name)
	{
		if (isA($element = reset($elements), Component::class)) {
			$getCompositeProperty = [get_class($element), 'getCompositeProperty'];
			if ($composite_property = call_user_func($getCompositeProperty, $composite_class_name)) {
				foreach ($elements as $element) {
					$property_name = $composite_property->name;
					$id_property_name = 'id_' . $property_name;
					if (isset($element->$property_name)) {
						$this->dao->disconnect($element->$property_name);
					}
					unset($element->$id_property_name);
				}
			}
		}
	}

}
