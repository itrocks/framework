<?php
namespace SAF\Framework\Reflection;

use SAF\Framework\Property;
use SAF\Framework\Reflection\Annotation\Class_\Link_Annotation;

/**
 * Link class
 */
class Link_Class extends Reflection_Class
{

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
			$link = $composite_object->getAnnotation('link');
			while ($link->value) {
				$composite_class_name = $link->value;
				$link = (new Link_Class($composite_class_name))->getAnnotation('link');
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
		return $this->getAnnotation('link')->value;
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
		$link = $this->getAnnotation('link');
		return $link->getLinkProperties();
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
			$class_name = $this->getAnnotation('link')->value;
		}
		foreach ($this->getLinkProperties() as $property_name) {
			$property = $this->getProperty($property_name);
			if (is_a($property->getType()->asString(), $class_name, true)) {
				return $property;
			}
		}
		return null;
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
		$link = $this->getAnnotation('link');
		return array_keys($link->getLinkProperties());
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
