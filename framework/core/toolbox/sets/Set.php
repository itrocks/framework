<?php
namespace SAF\Framework;

class Set
{

	//------------------------------------------------------------------------------------- $elements
	/**
	 * @var object[]
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
	 * @param $element_class_name string   the class name
	 * @param $elements           object[] the set can be initialized with this set of elements
	 */
	public function __construct($element_class_name = null, $elements = array())
	{
		$this->element_class_name = empty($element_class_name)
			? Names::setToClass(get_class($this))
			: $element_class_name;
		$this->elements = $elements;
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds an element to the set
	 *
	 * @param $key     string|object identity of the element in the set, or element if is null
	 * @param $element object|null $element element to add to the set (null if no or in key)
	 */
	public function add($key, $element = null)
	{
		if (isset($element)) {
			$this->elements[$key] = $element;
		}
		else {
			$this->elements[] = $key;
		}
	}

	//---------------------------------------------------------------------------------- elementClass
	public function elementClass()
	{
		return Reflection_Class::getInstanceOf($this->element_class_name);
	}

	//---------------------------------------------------------------------------- elementClassNameOf
	/**
	 * Gets element class name of a given set class name (namespace needed)
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function elementClassNameOf($class_name)
	{
		if (class_instanceof($class_name, __CLASS__)) {
			$class_name = (new $class_name())->element_class_name;
		}
		elseif (!class_exists($class_name)) {
			$class_name = Names::setToClass($class_name);
		}
		return $class_name;
	}

	//----------------------------------------------------------------------------------------- first
	public function first()
	{
		return reset($this->elements);
	}

	//------------------------------------------------------------------------------------------- get
	public function get($key)
	{
		return isset($this->elements[$key]) ? $this->elements[$key] : null;
	}

	//----------------------------------------------------------------------------------- instantiate
	/**
	 * Instantiates a set class name
	 *
	 * If this class does not exist, this will return a generic Set object constructed with
	 * the matching element class name.
	 *
	 * @param $class_name string
	 * @param array $elements
	 * @return Set
	 */
	public static function instantiate($class_name, $elements = array())
	{
		if (class_exists($class_name)) {
			return new $class_name($elements);
		}
		else {
			$element_class_name = static::elementClassNameOf($class_name);
			return new Set($element_class_name, $elements);
		}
	}

	//------------------------------------------------------------------------------------------ last
	public function last()
	{
		return end($this->elements);
	}

	//---------------------------------------------------------------------------------------- length
	public function length()
	{
		return count($this->elements);
	}

	//---------------------------------------------------------------------------------------- object
	public function object()
	{
		return $this->elements ? reset($this->elements) : $this->elementClass();
	}

}
