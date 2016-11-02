<?php
namespace SAF\Framework\Reflection;

use SAF\Framework\Reflection\Annotation\Class_\Link_Annotation;

/**
 * Link class
 */
class Link_Class extends Reflection_Class
{

	//---------------------------------------------------------------------------------- ID_SEPARATOR
	/**
	 * The separator for identifiers
	 */
	const ID_SEPARATOR = ';';

	//------------------------------------------------------------------------ getCompositeProperties
	/**
	 * @return Reflection_Property[]
	 */
	public function getCompositeProperties()
	{
		return call_user_func([$this->name, 'getCompositeProperties']);
	}

	//-------------------------------------------------------------------------- getCompositeProperty
	/**
	 * Returns the composite property that links to the redundant composite object
	 *
	 * @param $composite_class_name string to explicitly give the name of the linked class (faster)
	 * @return Reflection_Property
	 */
	public function getCompositeProperty($composite_class_name = null)
	{
		if (!isset($composite_class_name)) {
			$composite_object = $this;
			$link = $composite_object->getAnnotation(Link_Annotation::ANNOTATION);
			while ($link->value) {
				$composite_class_name = $link->value;
				$link = (new Link_Class($composite_class_name))->getAnnotation(Link_Annotation::ANNOTATION);
			}
		}
		/** @var $composite Reflection_Property[] */
		$composite = call_user_func([$this->name, 'getCompositeProperties'], $composite_class_name);
		return reset($composite);
	}

	//-------------------------------------------------------------------------------- getLinkedClass
	/**
	 * @return Link_Class
	 */
	public function getLinkedClass()
	{
		return new Link_Class($this->getLinkedClassName());
	}

	//---------------------------------------------------------------------------- getLinkedClassName
	/**
	 * @return string
	 */
	public function getLinkedClassName()
	{
		return $this->getAnnotation(Link_Annotation::ANNOTATION)->value;
	}

	//--------------------------------------------------------------------------- getLinkedProperties
	/**
	 * Returns properties list of the linked class, without those of the child class
	 *
	 * @return Reflection_Property[]
	 */
	public function getLinkedProperties()
	{
		return $this->getLinkedClass()->getProperties([T_EXTENDS, T_USE]);
	}

	//----------------------------------------------------------------------------- getLinkProperties
	/**
	 * Returns the two or more properties of the class that make the link
	 * ie : properties defined into the class @link annotation, if set,
	 * otherwise @composite properties
	 *
	 * @return Reflection_Property[] The key contains the name of the property
	 */
	public function getLinkProperties()
	{
		/** @var $link Link_Annotation */
		$link = $this->getAnnotation(Link_Annotation::ANNOTATION);
		/** @var $link_properties Reflection_Property[] */
		$link_properties = $link->getLinkProperties();
		return $link_properties;
	}

	//------------------------------------------------------------------------ getLinkPropertiesNames
	/**
	 * Returns the two or more properties names of the class that make the link
	 * ie : properties defined into the class @link annotation, if set,
	 * otherwise @composite properties names
	 *
	 * @return string[] key and value are the name of each link property
	 */
	public function getLinkPropertiesNames()
	{
		/** @var $link Link_Annotation */
		$link = $this->getAnnotation(Link_Annotation::ANNOTATION);
		return array_keys($link->getLinkProperties());
	}

	//------------------------------------------------------------------------------- getLinkProperty
	/**
	 * Returns the property of the class that make the link with the object of the parent class
	 *
	 * @param $class_name string
	 * @return Reflection_Property
	 */
	public function getLinkProperty($class_name = null)
	{
		if (!$class_name) {
			$class_name = $this->getAnnotation(Link_Annotation::ANNOTATION)->value;
		}
		foreach ($this->getLinkProperties() as $property_name) {
			$property = $this->getProperty($property_name);
			if (is_a($property->getType()->asString(), $class_name, true)) {
				return $property;
			}
		}
		return null;
	}

	//---------------------------------------------------------------------------- getLocalProperties
	/**
	 * Returns only properties of the class, without those of the linked class
	 * This includes properties that make the link
	 *
	 * @return Reflection_Property[]
	 */
	public function getLocalProperties()
	{
		$properties = [];
		$exclude = $this->getLinkedProperties();
		foreach ($this->getProperties([T_EXTENDS, T_USE]) as $property_name => $property) {
			if (!isset($exclude[$property_name])) {
				$properties[$property_name] = $property;
			}
		}
		return $properties;
	}

	//---------------------------------------------------------------------------- getRootLinkedClass
	/**
	 * Gets the root linked class, ie of the first parent class that has no @link
	 *
	 * This is the same as getLinkedClass(), with recursion.
	 * Another difference : if the current class is not a @link class, this will return $this.
	 *
	 * @return Link_Class
	 */
	public function getRootLinkedClass()
	{
		$linked_class = $this;
		do {
			$linked_class_name = $linked_class->getLinkedClassName();
			if ($linked_class_name) {
				$linked_class = new Link_Class($linked_class_name);
			}
		} while ($linked_class_name);
		return $linked_class;
	}

	//------------------------------------------------------------------------ getRootLinkedClassName
	/**
	 * Gets the name of the root linked class, ie of the first parent class that has no @link
	 *
	 * This is the same as getLinkedClassName(), with recursion
	 * Another difference : if the current class is not a @link class, this will return $this->name.
	 *
	 * @return string
	 */
	public function getRootLinkedClassName()
	{
		return $this->getRootLinkedClass()->name;
	}

	//--------------------------------------------------------------------------- getUniqueProperties
	/**
	 * Gets the list of @unique properties. If no @unique annotation, gets link properties
	 *
	 * @return Reflection_Property[] key is the name of the property
	 */
	public function getUniqueProperties()
	{
		$unique = $this->getListAnnotation('unique')->values();
		if ($unique) {
			$unique_properties = [];
			foreach ($unique as $property_name) {
				$unique_properties[$property_name] = $this->getProperty($property_name);
			}
		}
		else {
			$unique_properties = $this->getLinkProperties();
		}
		return $unique_properties;
	}

	//---------------------------------------------------------------------- getUniquePropertiesNames
	/**
	 * Gets the list of @unique property names. If no @unique annotation, gets link properties.
	 *
	 * @return string[] key and value are the name of each link property
	 */
	public function getUniquePropertiesNames()
	{
		return $this->getListAnnotation('unique')->values() ?: $this->getLinkPropertiesNames();
	}

	//----------------------------------------------------------------------------- linkedClassNameOf
	/**
	 * Gets the root linked class name of a class name or object
	 * If it is not a linked class, its name will simply be returned
	 *
	 * Use this tool method if you need to be sure the class name you use is not a link class name
	 *
	 * TODO LOW Works with one level linked classes only
	 *
	 * @param $class_name string|object The link or linked class name
	 * @return string The root linked class name
	 */
	public static function linkedClassNameOf($class_name)
	{
		if (is_object($class_name)) {
			$class_name = get_class($class_name);
		}
		return (new Link_Class($class_name))->getLinkedClassName() ?: $class_name;
	}

}
