<?php
namespace ITRocks\Framework\Dao;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link\Identifier_Map;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;

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
	private Identifier_Map $dao;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * The constructor can be called with a specific data link.
	 * The default data link will be Dao::current()
	 * This works only for Identifier_Map data links
	 *
	 * @param $dao Identifier_Map|null
	 */
	public function __construct(Identifier_Map $dao = null)
	{
		$this->dao = $dao ?? Dao::current();
	}

	//------------------------------------------------------------------------------- createDuplicate
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 */
	public function createDuplicate(object $object) : void
	{
		if (!$this->dao->getObjectIdentifier($object)) {
			return;
		}
		// duplicate @link Collection and Map properties values
		$class_name = get_class($object);
		/** @noinspection PhpUnhandledExceptionInspection get_class from object */
		$class = new Reflection_Class($class_name);
		$link  = Class_\Link_Annotation::of($class);
		/** @noinspection PhpUnhandledExceptionInspection link annotation value must be valid */
		$exclude_properties = $link->value
			? array_keys((new Reflection_Class($link->value))->getProperties([T_EXTENDS, T_USE]))
			: [];
		foreach ($class->getProperties() as $property) {
			if (!$property->getAnnotation('duplicate')->value) {
				$property->setValue($object, $property->getDefaultValue());
				continue;
			}
			if ($property->isStatic() || in_array($property->name, $exclude_properties, true)) {
				continue;
			}
			$property_link = Link_Annotation::of($property);
			// @link Collection : must disconnect objects
			// @link Collection | Map : duplicate and remove reference to the parent id
			if (!$property_link->is(Link_Annotation::COLLECTION, Link_Annotation::MAP)) {
				continue;
			}
			/** @noinspection PhpUnhandledExceptionInspection property from object and accessible */
			$elements = $property->getValue($object);
			if ($property_link->isCollection()) {
				foreach ($elements as $element) {
					$this->createDuplicate($element);
				}
			}
			$this->removeCompositeFromComponents($elements, $class_name);
		}
		// duplicate object
		$this->dao->disconnect($object);
		// after duplicate
		$this->onDuplicate($object, $class);
	}

	//----------------------------------------------------------------------------------- onDuplicate
	/**
	 * Call onDuplicate methods defined by @duplicate class annotation
	 *
	 * @param $object object
	 * @param $class  Reflection_Class
	 */
	private function onDuplicate(object $object, Reflection_Class $class) : void
	{
		foreach (array_reverse($class->getAnnotations('duplicate')) as $on_duplicate) {
			$callback = explode('::', $on_duplicate->value);
			// is_numeric : probably '1' for true
			if (is_numeric($callback[1])) {
				$callback[1] = 'onDuplicate';
			}
			if (isA($object, $callback[0])) {
				// TODO LOW check if this is used somewhere : if not, remove
				if (str_contains($callback[1], ',')) {
					$callbacks = explode(',', $callback[1]);
					foreach ($callbacks as $callback_function) {
						$callback_function = trim($callback_function);
						call_user_func([$object, $callback_function]);
					}
				}
				else {
					call_user_func([$object, $callback[1]]);
				}
			}
			else {
				call_user_func([$callback[0], $callback[1]], $object);
			}
		}
	}

	//----------------------------------------------------------------- removeCompositeFromComponents
	/**
	 * Remove reference to parent ids ($id_parent) from collection / map components
	 *
	 * @param $elements             object[]|Component[] the component objects
	 * @param $composite_class_name string the composite class name
	 */
	private function removeCompositeFromComponents(array $elements, string $composite_class_name)
		: void
	{
		if (!isA($element = reset($elements), Component::class)) {
			return;
		}
		$get_composite_property_method = [get_class($element), 'getCompositeProperty'];
		$composite_property = call_user_func($get_composite_property_method, $composite_class_name);
		if (!$composite_property) {
			return;
		}
		foreach ($elements as $element) {
			$id_property_name = 'id_' . $composite_property->name;
			unset($element->$id_property_name);
		}
	}

}
