<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Field\Final_Text;
use ITRocks\Framework\Layout\Structure\Field\Property;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Page;
use ITRocks\Framework\Property\Reflection_Property;
use ReflectionException;

/**
 * Property to final text elements
 */
class Property_To_Text
{
	use Has_Structure;

	//-------------------------------------------------------------------------------------- $already
	/**
	 * Tells which properties, into groups, have already been generated
	 *
	 * @var boolean[] key is the $property_path
	 */
	protected $already;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * The object containing the data
	 *
	 * @var object
	 */
	protected $object;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Properties cache
	 *
	 * @var Reflection_Property[]
	 */
	protected $properties;

	//--------------------------------------------------------------------------------------- element
	/**
	 * Process a Property element
	 *
	 * @noinspection PhpDocMissingThrowsInspection getValue
	 * @param $element Property
	 */
	protected function element(Property $element)
	{
		// This is a 'linear' algorithm, not recursive, to go faster : objects list grow during descend
		$objects = [$this->object];
		foreach (explode(DOT, $element->property_path) as $property_name) {
			$next_objects = [];
			foreach ($objects as $object) {
				try {
					$property = new Reflection_Property(get_class($object), $property_name);
				}
				// bad property.path : no data, ignore the element
				catch (ReflectionException $exception) {
					return;
				}
				/** @noinspection PhpUnhandledExceptionInspection tested by new Reflection_Property */
				$object = $property->getValue($object);
				if (is_array($object)) {
					$next_objects = array_merge($next_objects, $object);
				}
				else {
					$next_objects[] = $object;
				}
			}
			$objects = $next_objects;
		}
		// Create one Final_Text per final object
		$page = $element->page;
		foreach ($objects as $object) {
			$new_element = new Final_Text($page);
			foreach (get_object_vars($element) as $property_name => $value) {
				if (property_exists($new_element, $property_name)) {
					$new_element->$property_name = $value;
				}
			}
			$new_element->element = $element;
			$new_element->text    = $object;
			if ($element->group) {
				$element->group->elements[] = $new_element;
			}
			else {
				$page->elements[] = $new_element;
			}
		}
	}

	//----------------------------------------------------------------------------------------- group
	/**
	 * Process a group
	 *
	 * @param $group Group
	 */
	protected function group(Group $group)
	{
		foreach ($group->elements as $key => $element) {
			if ($element instanceof Property) {
				if (!isset($this->already[$element->property_path])) {
					unset($group->elements[$key]);
					$this->already[$element->property_path] = true;
					$this->element($element);
				}
			}
			elseif ($element instanceof Group) {
				$this->group($element);
			}
		}
	}

	//------------------------------------------------------------------------------------------ page
	/**
	 * Process a page
	 *
	 * @param $page Page
	 */
	protected function page(Page $page)
	{
		foreach ($page->elements as $key => $element) {
			if ($element instanceof Property) {
				unset($page->elements[$key]);
				$this->element($element);
			}
			elseif ($element instanceof Group) {
				$this->group($element);
			}
		}
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Process the structure
	 *
	 * Scan structure elements for properties and transform them to single or multiple Final_Text
	 *
	 * @param $object object
	 */
	public function run($object)
	{
		$this->object = $object;
		foreach ($this->structure->pages as $page) {
			$this->page($page);
		}
	}

}
