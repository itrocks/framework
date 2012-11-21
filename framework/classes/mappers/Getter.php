<?php
namespace SAF\Framework;

abstract class Getter
{

	//--------------------------------------------------------------------------------- getCollection
	/**
	 * Generic getter for a collection of objects
	 *
	 * @param multitype:Contained $collection    actual value of the property (will be returned if not null)
	 * @param string              $element_class the class for each collection's object 
	 * @param object              $parent        the parent object
	 * @return multitype:object
	 */
	public static function getCollection($collection, $element_class, $parent)
	{
		if (!isset($collection)) {
			$search_element = Search_Object::newInstance($element_class);
			if ($search_element instanceof Contained) {
				$search_element->setParent($parent);
			}
			$collection = Dao::search($search_element);
			if ($search_element instanceof Contained) {
				// this to avoid getter calls on $element->getParent() call (parent is already loaded)
				foreach ($collection as $element) {
					$element->setParent($parent);
				}
			}
		}
		return $collection;
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Generic getter for an object
	 *
	 * @param mixed  $object       actual value of the object (will be returned if already an object)
	 * @param string $object_class the object class
	 */
	public static function getObject($object, $object_class)
	{
		if (!is_object($object)) {
			$object = Dao::read($object, $object_class);
		}
		return $object;
	}

}
