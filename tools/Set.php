<?php
namespace ITRocks\Framework\Tools;

use Iterator;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * The default Set class for set of objects
 */
class Set implements Iterator
{

	//--------------------------------------------------------------------------- $element_class_name
	/**
	 * Element class name, with namespace
	 *
	 * @var string
	 */
	public string $element_class_name;

	//------------------------------------------------------------------------------------- $elements
	/**
	 * @var object[]
	 */
	public array $elements;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a Set object for given element class name
	 *
	 * @param $element_class_name string|null the name of the class
	 * @param $elements           object[] the set can be initialized with this set of elements
	 */
	public function __construct(string $element_class_name = null, array $elements = [])
	{
		$this->element_class_name = empty($element_class_name)
			? Names::setToClass(get_class($this))
			: $element_class_name;
		$this->elements = $elements;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return Names::classToDisplay($this->element_class_name);
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds an element into the set, if not already present (same key)
	 *
	 * @param $key     integer|object|string identity of the element in the set, or element if is null
	 * @param $element object|null $element element to add to the set (null if no or in key)
	 */
	public function add(int|object|string $key, object $element = null) : void
	{
		if (isset($element)) {
			$this->elements[$key] = $element;
		}
		else {
			$this->elements[] = $key;
		}
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * Return the value of the current element designed by the pointer of the set
	 *
	 * @return object
	 */
	public function current() : object
	{
		return current($this->elements);
	}

	//---------------------------------------------------------------------------------- elementClass
	/**
	 * Get element class reflection object for the current element class name
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class
	 */
	public function elementClass() : Reflection_Class
	{
		/** @noinspection PhpUnhandledExceptionInspection $element_class_name must always be valid */
		return new Reflection_Class($this->element_class_name);
	}

	//---------------------------------------------------------------------------- elementClassNameOf
	/**
	 * Get element class name of a given set class name (namespace needed)
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function elementClassNameOf(string $class_name) : string
	{
		if (is_a($class_name, __CLASS__, true)) {
			$class_name = (new $class_name)->element_class_name;
		}
		elseif (!class_exists($class_name)) {
			$class_name = Names::setToClass($class_name, false);
		}
		return Builder::className($class_name);
	}

	//--------------------------------------------------------------------------------- filterAndSort
	/**
	 * @param $filter_elements string[] each filter element is the key into the elements list
	 * @param $change          boolean if true, the Set elements are update, if false, the filtered
	 *        and sorted elements list is returned without changing the set
	 * @return object[] filtered and sorted array of elements
	 */
	public function filterAndSort(array $filter_elements, bool $change = true) : array
	{
		$sorted_elements = [];
		foreach ($filter_elements as $element_key) {
			if (isset($this->elements[$element_key])) {
				$sorted_elements[$element_key] = $this->elements[$element_key];
			}
		}
		if ($change) {
			$this->elements = $sorted_elements;
		}
		return $sorted_elements;
	}

	//----------------------------------------------------------------------------------------- first
	/**
	 * Set the pointer of the set to its first element and return this element (alias for rewind)
	 *
	 * @return ?object
	 */
	public function first() : ?object
	{
		return reset($this->elements);
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Get the element associated to the key from the set
	 *
	 * @param $key integer|string
	 * @return ?object
	 */
	public function get(int|string $key) : ?object
	{
		return $this->elements[$key] ?? null;
	}

	//----------------------------------------------------------------------------------- instantiate
	/**
	 * Instantiates a set class name
	 *
	 * If this class does not exist, this will return a generic Set object constructed with
	 * the matching element class name.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $elements   object[]
	 * @return Set
	 */
	public static function instantiate(string $class_name, array $elements = []) : Set
	{
		if (class_exists($class_name) && is_a($class_name, Set::class, true)) {
			return new $class_name($elements);
		}
		/** @noinspection PhpUnhandledExceptionInspection trait $class_name exists tested */
		elseif (
			trait_exists($class_name)
			&& ($extends_classes = Extend::oneOf(new Reflection_Class($class_name))->extends)
		) {
			$extends_class = reset($extends_classes);
			return new $extends_class($elements);
		}
		else {
			$element_class_name = static::elementClassNameOf($class_name);
			return new Set($element_class_name, $elements);
		}
	}

	//------------------------------------------------------------------------------------------- key
	/**
	 * Return the key of the current element designed by the pointer of the set
	 *
	 * @return int|string|null
	 */
	public function key() : int|string|null
	{
		return key($this->elements);
	}

	//------------------------------------------------------------------------------------------ last
	/**
	 * Set the pointer of the set into the last element and return this element
	 *
	 * @return ?object
	 */
	public function last() : ?object
	{
		return end($this->elements);
	}

	//---------------------------------------------------------------------------------------- length
	/**
	 * Return the number of elements stored into the set
	 *
	 * @return integer
	 */
	public function length() : int
	{
		return count($this->elements);
	}

	//------------------------------------------------------------------------------------------ next
	/**
	 * Set the pointer of the set into the next element and return this element
	 */
	public function next() : void
	{
		next($this->elements);
	}

	//---------------------------------------------------------------------------------------- object
	/**
	 * Get the first object, or a Reflection_Class of the object's class if no element
	 *
	 * @return object
	 */
	public function object() : object
	{
		return $this->elements ? reset($this->elements) : $this->elementClass();
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Get the element associated to the key from the set
	 *
	 * @param $key integer|string
	 */
	public function remove(int|string $key) : void
	{
		unset($this->elements[$key]);
	}

	//---------------------------------------------------------------------------------------- rewind
	/**
	 * Set the pointer of the set to its first element and return this element (alias for first)
	 */
	public function rewind() : void
	{
		reset($this->elements);
	}

	//----------------------------------------------------------------------------------------- valid
	/**
	 * Return true if an element is currently selected
	 *
	 * @return boolean
	 */
	public function valid() : bool
	{
		return (bool)current($this->elements);
	}

}
