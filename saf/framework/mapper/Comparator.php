<?php
namespace SAF\Framework\Mapper;

use SAF\Framework\Reflection\Reflection_Class;

/**
 * This
 */
class Comparator
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	private $class_name;

	//------------------------------------------------------------------------------ $properties_path
	/**
	 * @var string[]
	 */
	private $properties_path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name      string
	 * @param $properties_path string[]
	 */
	public function __construct($class_name, $properties_path = [])
	{
		$this->class_name = $class_name;
		if ($properties_path) {
			$this->properties_path = $properties_path;
		}
		else {
			$class = new Reflection_Class($class_name);
			$this->properties_path = $class->getListAnnotation('sort')->values();
		}
	}

	//--------------------------------------------------------------------------------------- compare
	/**
	 * Returns -1 if $object1 < $object2, 1 if $object1 > $object2, or 0 if $object1 == $object2
	 *
	 * @param $object1 object
	 * @param $object2 object
	 * @return integer -1, 0 or 1
	 */
	public function compare($object1, $object2)
	{
		if (is_object($object1) && is_object($object2)) {
			// Comparable objects : use their compare method
			if (
				($object1 instanceof Comparable)
				&& (is_a($object1, get_class($object2)) || is_a($object2, get_class($object1)))
			) {
				return call_user_func_array([get_class($object1), 'compare'], [$object1, $object2]);
			}
			// compare values of properties path of two objects and stop comparison once a found difference
			else {
				$path1 = new Object_Property_Path($object1);
				$path2 = new Object_Property_Path($object2);
				foreach ($this->properties_path as $property_path) {
					$value1 = $path1->getValue($property_path);
					$value2 = $path2->getValue($property_path);
					if (is_object($value1) || is_object($value2)) {
						$comparator = new Comparator(get_class($value1));
						$result     = $comparator->compare($value1, $value2);
					}
					else {
						$result = ($value1 < $value2) ? -1 : (($value1 > $value2) ? 1 : 0);
					}
					if ($result) {
						return $result;
					}
				}
				return 0;
			}
		}
		// at least one of the two values is not an object : compare as values
		if (is_object($object1)) $object1 = strval($object1);
		if (is_object($object2)) $object2 = strval($object2);
		return ($object1 < $object2) ? -1 : (($object1 > $object2) ? 1 : 0);
	}

	//------------------------------------------------------------------------------------------ sort
	/**
	 * Sort a collection of compatible objects
	 *
	 * @param $objects object[]
	 * @param $sort_callback string You can define which sort function to call
	 */
	public function sort(&$objects, $sort_callback = 'uasort')
	{
		call_user_func_array($sort_callback, [&$objects, [$this, 'compare']]);
	}

}
