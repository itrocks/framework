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
	 * @var string
	 */
	public $element_class_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a Set object for given element class name
	 *
	 * @param string $element_class_name
	 */
	public function __construct($element_class_name)
	{
		$this->element_class_name = $element_class_name;
	}

	//---------------------------------------------------------------------------- elementClassNameOf
	/**
	 * Gets element class name of a given set class name
	 *
	 * @param string $class_name
	 * @return string
	 */
	public static function elementClassNameOf($class_name)
	{
		if (class_exists($class_name)) {
			$set = new $class_name();
			return $set->element_class_name;
		}
		elseif (substr($class_name, -3) == "ies") {
			return substr($class_name, 0, -3) . "y";
		}
		elseif (substr($class_name, -1) == "s") {
			return substr($class_name, 0, -1);
		}
		else {
			return $class_name;
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
