<?php
namespace SAF\Framework;

/**
 * Link class
 */
class Link_Class extends Reflection_Class
{

	//-------------------------------------------------------------------------- getCompositeProperty
	/**
	 * Returns the composite property that links to the redundant composite object
	 *
	 * @return Reflection_Property
	 */
	public function getCompositeProperty()
	{
		/** @var $composite Reflection_Property[] */
		$composite = call_user_func(array($this->name, "getCompositeProperties"), $this->name);
		return reset($composite);
	}

	//---------------------------------------------------------------------------------- getLinkClass
	/**
	 * @return Reflection_Class
	 */
	public function getLinkClass()
	{
		return Reflection_Class::getInstanceOf($this->getLinkClassName());
	}

	//------------------------------------------------------------------------------ getLinkClassName
	/**
	 * @return string
	 */
	public function getLinkClassName()
	{
		return $this->getAnnotation("link")->value;
	}

	//----------------------------------------------------------------------------- getLinkProperties
	/**
	 * Returns properties list of the linked class, without those of the child class
	 *
	 * @return Reflection_Property[]
	 */
	public function getLinkProperties()
	{
		return $this->getLinkClass()->getAllProperties();
	}

	//---------------------------------------------------------------------------- getLocalProperties
	/**
	 * Returns only properties of the class, without those of the linked class
	 *
	 * @return Reflection_Property[]
	 */
	public function getLocalProperties()
	{
		$properties = array();
		$exclude = $this->getLinkProperties();
		foreach ($this->getAllProperties() as $property_name => $property) {
			if (!isset($exclude[$property_name])) {
				$properties[$property_name] = $property;
			}
		}
		return $properties;
	}

}
