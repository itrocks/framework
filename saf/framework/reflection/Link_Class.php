<?php
namespace SAF\Framework\Reflection;

/**
 * Link class
 */
class Link_Class extends Reflection_Class
{

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

	//---------------------------------------------------------------------------- getLocalProperties
	/**
	 * Returns only properties of the class, without those of the linked class
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

}
