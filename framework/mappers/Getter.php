<?php
namespace SAF\Framework;

class Getter
{

	//--------------------------------------------------------------------------------- getCollection
	/**
	 * 
	 * @param multitype:Container $collection
	 * @param string              $elementClass
	 * @param object              $parent
	 * @return multitype:object
	 */
	public static function getCollection($collection, $element_class, $parent)
	{
		if ($collection == null) {
			$search_element = Search_Object::newInstance($element_class);
			$search_element->setParent($parent);
			$collection = Dao::search($search_element);
			// this to avoir getter call on $element->getParent() call (parent is already loaded)
			foreach ($collection as $element) {
				$element->setParent($parent);
			}
		}
		return $collection;
	}

	//------------------------------------------------------------------------- getGetterForFieldName
	/**
	 * @param  string $field_name
	 * @return string
	 */
	private static function getDefaultGetterForFieldName($field_name)
	{
		return Names::propertyToMethod($field_name, "get");
	}

	//------------------------------------------------------------------------------------- getGetter
	/**
	 * @param  mixed $field Reflection_Property, string
	 * @return mixed Reflection_Method, string
	 */
	public static function getGetter($field)
	{
		if ($field instanceof Reflection_Property) {
			return Getter::getGetterForField($field);
		} else {
			return Getter::getDefaultGetterForFieldName($field);
		}
	}

	//----------------------------------------------------------------------------- getGetterForField
	/**
	 * @param  Reflection_Property $field
	 * @return Reflection_Method
	 */
	private static function getGetterForField($field)
	{
		$method_name = "";
		$annotation = $field->getGetterName();
		if ($annotation) {
			$method_name = $annotation->value;
		} else {
			$method_name = Getter::getDefaultGetterForFieldName($field->getName());
		}
		$getter = null;
		$object_class = $field->getDeclaringClass();
		while ($object_class && !$getter) {
			$getter = $object_class->getMethod($method_name);
			if (!$getter) {
				$object_class = $object_class->getParentClass();
			}
		}
		return $getter;
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * @param  mixed  $object
	 * @param  string $object_class
	 */
	public static function getObject($object, $object_class)
	{
		if (is_int($object)) {
			$object = Dao::read($object, $object_class);
		}
		return $object;
	}

}
