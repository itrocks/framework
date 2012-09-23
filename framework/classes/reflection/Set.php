<?php
namespace SAF\Framework;

class Set
{

	//------------------------------------------------------------------------------------- $elements
	/**
	 * @var multitype:object
	 */
	public $elements;

	//--------------------------------------------------------------------------- $element_class_name
	/**
	 * Element class name, with namespace
	 *
	 * @var string
	 */
	public $element_class_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a Set object for given element class name
	 *
	 * @param string $element_class_name
	 */
	public function __construct($element_class_name = null)
	{
		$this->element_class_name = $element_class_name
			? $element_class_name
			: Names::setToClass(get_class($this));
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds an element to the set
	 *
	 * @param string | object $key identity of the element in the set, or element if $element is null
	 * @param object | null $element element to add to the set (null if no $key)
	 */
	public function add($key, $element = null)
	{
		if ($element) {
			$this->elements[$key] = $element;
		} else {
			$this->elements[] = $element;
		}
	}

	//---------------------------------------------------------------------------- elementClassNameOf
	/**
	 * Gets element class name of a given set class name (namespace needed)
	 *
	 * @param string $class_name
	 * @return string
	 */
	public static function elementClassNameOf($class_name)
	{
		if (class_exists($class_name)) {
			$set = new $class_name();
			return $set->element_class_name;
		} else {
			return Namespaces::fullClassName(Names::setToClass($class_name));
		}
	}

	//----------------------------------------------------------------------------------- instantiate
	/**
	 * Instantiates a set class name
	 *
	 * If this class does not exist, this will return a generic Set object constructed with
	 * the matching element class name.
	 *
	 * @param string $class_name
	 * @return Set
	 */
	public static function instantiate($class_name)
	{
		if (class_exists($class_name)) {
			return new $class_name();
		}
		else {
			$element_class_name = Set::elementClassNameOf($class_name);
			return new Set($element_class_name);
		}
	}

}
