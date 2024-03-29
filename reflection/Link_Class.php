<?php
namespace ITRocks\Framework\Reflection;

use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_\Unique;
use ITRocks\Framework\Reflection\Attribute\Property\Composite;

/**
 * Link class
 */
class Link_Class extends Reflection_Class
{

	//---------------------------------------------------------------------------------- ID_SEPARATOR
	/** The separator for identifiers */
	const ID_SEPARATOR = ';';

	//--------------------------------------------------------------------------- $link_property_name
	/** Set to force the property name which value is the linked object (to avoid conflicts) */
	public string $link_property_name = '';

	//------------------------------------------------------------------------ getCompositeProperties
	/** @return Reflection_Property[] */
	public function getCompositeProperties() : array
	{
		return call_user_func([$this->name, 'getCompositeProperties']);
	}

	//-------------------------------------------------------------------------- getCompositeProperty
	/**
	 * Returns the composite property that links to the redundant composite object
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $composite_class_name string to explicitly give the name of the linked class (faster)
	 * @param $component_object     boolean Can be false to ignore warning on multiple composites
	 * @return Reflection_Property
	 */
	public function getCompositeProperty(
		string $composite_class_name = '', bool $component_object = true
	) : Reflection_Property
	{
		if (!$composite_class_name) {
			$composite_object = $this;
			$link = Link_Annotation::of($composite_object);
			while ($link->value) {
				$composite_class_name = $link->value;
				/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
				$link = Link_Annotation::of(new Link_Class($composite_class_name));
			}
		}
		/** @var $composite_properties Reflection_Property[] */
		$composite_properties = call_user_func(
			[$this->name, 'getCompositeProperties'], $composite_class_name
		);
		if (count($composite_properties) > 1) {
			if (!$component_object) {
				$composite_properties = [];
			}
			elseif ($this->link_property_name) {
				unset($composite_properties[$this->link_property_name]);
			}
			if (count($composite_properties) > 1) {
				trigger_error(
					'Several properties can be composite : ' . join(', ', array_keys($composite_properties)),
					E_USER_WARNING
				);
			}
		}
		return reset($composite_properties);
	}

	//----------------------------------------------------------------------------- getLinkProperties
	/**
	 * Returns the two or more properties of the class that make the link
	 * ie : properties defined into the class @link annotation, if set,
	 * otherwise #Composite properties
	 *
	 * @return Reflection_Property[] The key contains the name of the property
	 */
	public function getLinkProperties() : array
	{
		return Link_Annotation::of($this)->getLinkProperties();
	}

	//------------------------------------------------------------------------ getLinkPropertiesNames
	/**
	 * Returns the two or more properties names of the class that make the link
	 * ie : properties defined into the class @link annotation, if set,
	 * otherwise #Composite properties names
	 *
	 * @return string[] key and value are the name of each link property
	 */
	public function getLinkPropertiesNames() : array
	{
		return array_keys(Link_Annotation::of($this)->getLinkProperties());
	}

	//------------------------------------------------------------------------------- getLinkProperty
	/** Returns the property of the class that make the link with the object of the parent class */
	public function getLinkProperty(string $class_name = '') : ?Reflection_Property
	{
		if (!$class_name) {
			$class_name = Link_Annotation::of($this)->value;
		}
		foreach ($this->getLinkProperties() as $property) {
			/** @noinspection PhpUnhandledExceptionInspection getLinkProperties gets valid properties */
			$property = $this->getProperty($property->name);
			if (is_a($property->getType()->asString(), $class_name, true)) {
				return $property;
			}
		}
		return null;
	}

	//-------------------------------------------------------------------------------- getLinkedClass
	public function getLinkedClass() : Link_Class
	{
		/** @noinspection PhpUnhandledExceptionInspection linked class name is always valid */
		return new Link_Class($this->getLinkedClassName());
	}

	//---------------------------------------------------------------------------- getLinkedClassName
	public function getLinkedClassName() : string
	{
		return Link_Annotation::of($this)->value;
	}

	//--------------------------------------------------------------------------- getLinkedProperties
	/** @return Reflection_Property[] Linked class property list, without those of the child class */
	public function getLinkedProperties() : array
	{
		return $this->getLinkedClass()->getProperties([T_EXTENDS, T_USE]);
	}

	//---------------------------------------------------------------------------- getLocalProperties
	/**
	 * Returns only properties of the class, without those of the linked class
	 * This includes properties that make the link
	 *
	 * @return Reflection_Property[]
	 */
	public function getLocalProperties() : array
	{
		$properties = [];
		$exclude    = $this->getLinkedProperties();
		foreach ($this->getProperties([T_EXTENDS, T_USE]) as $property_name => $property) {
			if (!isset($exclude[$property_name]) && !$property->isStatic()) {
				$properties[$property_name] = $property;
			}
		}
		return $properties;
	}

	//---------------------------------------------------------------------------- getRootLinkedClass
	/**
	 * Gets the root linked class, ie of the first parent class that has no link annotation
	 *
	 * This is the same as getLinkedClass(), with recursion.
	 * Another difference : if the current class is not a link class, this will return $this.
	 */
	public function getRootLinkedClass() : Link_Class
	{
		$linked_class = $this;
		do {
			$linked_class_name = $linked_class->getLinkedClassName();
			if ($linked_class_name) {
				/** @noinspection PhpUnhandledExceptionInspection linked class name is always valid */
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
	 */
	public function getRootLinkedClassName() : string
	{
		return $this->getRootLinkedClass()->name;
	}

	//--------------------------------------------------------------------------- getUniqueProperties
	/**
	 * Gets the list of #Unique properties. If no #Unique annotation, gets link properties
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Property[] key is the name of the property
	 */
	public function getUniqueProperties() : array
	{
		$unique = Unique::of($this)?->values;
		if (!$unique) {
			return $this->getLinkProperties();
		}
		$unique_properties = [];
		foreach ($unique as $property_name) {
			/** @noinspection PhpUnhandledExceptionInspection should not have #Unique bad_property */
			$unique_properties[$property_name] = $this->getProperty($property_name);
		}
		return $unique_properties;
	}

	//---------------------------------------------------------------------- getUniquePropertiesNames
	/**
	 * Gets the list of #Unique property names. If no #Unique annotation, gets link properties.
	 *
	 * @return string[] key and value are the name of each link property
	 */
	public function getUniquePropertiesNames() : array
	{
		return Unique::of($this)?->values ?: $this->getLinkPropertiesNames();
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name object|string The link or linked class name
	 * @return string The root linked class name
	 */
	public static function linkedClassNameOf(object|string $class_name) : string
	{
		if (is_object($class_name)) {
			$class_name = get_class($class_name);
		}
		/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
		return (new Link_Class($class_name))->getLinkedClassName() ?: $class_name;
	}

	//---------------------------------------------------------------------------------- searchObject
	/**
	 * Gets a search object matching the link object's identifier
	 *
	 * - Only unique properties are kept into the search object
	 * - If $strict is true, null will be returned if any of the composite properties has no value
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return ?object The search object matching $object, with only identifiers set
	 */
	public static function searchObject(object $object, bool $strict = true) : ?object
	{
		$search = Search_Object::create(get_class($object));
		/** @noinspection PhpUnhandledExceptionInspection object */
		$link = new Link_Class($object);
		foreach ($link->getUniqueProperties() as $property) {
			/** @noinspection PhpUnhandledExceptionInspection $property from object must be accessible */
			$value = $property->getValue($object);
			if ($strict && empty($value) && Composite::of($property)?->value) {
				return null;
			}
			$property->setValue($search, $value);
		}
		return $search;
	}

}
