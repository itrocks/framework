<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Field\Final_Text;
use ITRocks\Framework\Layout\Structure\Field\Property;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Group\Iteration;
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
	 * Tells which properties text, into groups, have already been generated
	 *
	 * @var boolean[] key is the $property_path
	 */
	protected $already;

	//----------------------------------------------------------------------------------- $iterations
	/**
	 * Already generated iterations into the current page groups
	 *
	 * @var Iteration[][] Iteration[string $group_property_path][integer $iteration_number]
	 */
	protected $iterations;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * The object containing the data
	 *
	 * @var object
	 */
	protected $object;

	//---------------------------------------------------------------------------------------- append
	/**
	 * @param $final_text       Final_Text
	 * @param $iteration_number integer
	 */
	protected function append(Final_Text $final_text, $iteration_number)
	{
		// append element to the group iteration / page
		if ($final_text->group) {
			$iteration             = $this->iteration($final_text->group, $iteration_number);
			$final_text->iteration = $iteration;
			$iteration->elements[] = $final_text;
		}
		else {
			$final_text->page->elements[] = $final_text;
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
		foreach ($group->groups as $sub_group) {
			$this->group($sub_group);
		}
		foreach ($group->properties as $key => $property) {
			if (!isset($this->already[$property->property_path])) {
				unset($group->properties[$key]);
				$this->already[$property->property_path] = true;
				$this->property($property);
			}
		}
	}

	//------------------------------------------------------------------------------------- iteration
	/**
	 * Get / create the iteration identified by the group and number
	 *
	 * @param $group            Group
	 * @param $iteration_number integer
	 * @return Iteration
	 */
	protected function iteration(Group $group, $iteration_number)
	{
		if (!isset($this->iterations[$group->property_path][$iteration_number])) {
			$iteration           = new Iteration($group->page);
			$iteration->group    = $group;
			$iteration->left     = $group->left;
			$iteration->number   = $iteration_number;
			$iteration->top      = $group->top;
			$iteration->width    = $group->width;
			$group->iterations[] = $iteration;

			$this->iterations[$group->property_path][$iteration_number] = $iteration;
			return $iteration;
		}
		return $this->iterations[$group->property_path][$iteration_number];
	}

	//----------------------------------------------------------------------------------- nextObjects
	/**
	 * @param $objects       object[]
	 * @param $property_name string
	 * @return object[]
	 * @throws ReflectionException
	 */
	protected function nextObjects(array $objects, $property_name)
	{
		$next_objects = [];
		if ($objects) {
			$reflection_property = new Reflection_Property(get_class(reset($objects)), $property_name);
			foreach ($objects as $object) {
				/** @noinspection PhpUnhandledExceptionInspection must be valid here */
				$object = $reflection_property->getValue($object);
				if (is_array($object)) {
					$next_objects = array_merge($next_objects, $object);
				}
				elseif (is_object($object) || strlen($object)) {
					$next_objects[] = $object;
				}
			}
		}
		return $next_objects;
	}

	//------------------------------------------------------------------------------------------ page
	/**
	 * Process a page
	 *
	 * @param $page Page
	 */
	protected function page(Page $page)
	{
		$this->iterations = [];
		foreach ($page->groups as $group) {
			$this->group($group);
		}
		foreach ($page->properties as $property_key => $property) {
			unset($page->elements[$property_key]);
			$this->property($property);
		}
	}

	//-------------------------------------------------------------------------------------- property
	/**
	 * Process a Property element
	 *
	 * @noinspection PhpDocMissingThrowsInspection getValue
	 * @param $property Property
	 */
	protected function property(Property $property)
	{
		// Create one Final_Text per final object
		foreach ($this->values($property) as $iteration_number => $value) {
			$final_text = $this->propertyToFinalText($property, $value);
			$this->append($final_text, $iteration_number);
		}
	}

	//--------------------------------------------------------------------------- propertyToFinalText
	/**
	 * @param $property Property
	 * @param $value    string
	 * @return Final_Text
	 */
	protected function propertyToFinalText(Property $property, $value)
	{
		// change property to final text
		$final_text = new Final_Text($property->page);
		foreach (get_object_vars($property) as $property_name => $property_value) {
			if (property_exists($final_text, $property_name)) {
				$final_text->$property_name = $property_value;
			}
		}
		$final_text->property = $property;
		$final_text->text     = $value;
		// initialize final text, force height calculation
		$final_text->height = 0;
		$final_text->init();

		return $final_text;
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

	//---------------------------------------------------------------------------------------- values
	/**
	 * Descend through property values to 'explode' it into all matching objects
	 *
	 * @param $property Property
	 * @return object[]
	 */
	protected function values(Property $property)
	{
		// This is a 'linear' algorithm, not recursive, to go faster : objects list grow during descend
		$objects = [$this->object];
		foreach (explode(DOT, $property->property_path) as $property_name) {
			try {
				$objects = $this->nextObjects($objects, $property_name);
			}
			catch (ReflectionException $exception) {
				// bad property.path : no data, ignore the element
				return [];
			}
		}
		return $objects;
	}

}
