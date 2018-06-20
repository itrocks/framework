<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure;
use ITRocks\Framework\Layout\Structure\Field\Final_Text;
use ITRocks\Framework\Layout\Structure\Field\Property;
use ITRocks\Framework\Layout\Structure\Page;
use ITRocks\Framework\Property\Reflection_Property;
use ReflectionException;

/**
 * Property to final text elements
 */
class Property_To_Text
{

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

	//------------------------------------------------------------------------------------ $structure
	/**
	 * The structure that first contains Property, and finally will contain Final_Text instead
	 * Only data of Final_Text will be filled : coordinates will not be recalculated at this step
	 *
	 * @var Structure
	 */
	protected $structure;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $structure Structure
	 */
	public function __construct(Structure $structure)
	{
		$this->structure = $structure;
	}

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
				} // bad property.path : no data, ignore the element
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
			$page->elements[]     = $new_element;
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
