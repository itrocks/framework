<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Layout\Generator\Text_Templating\Parser;
use ITRocks\Framework\Layout\Structure;
use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Field;
use ITRocks\Framework\Layout\Structure\Field\Final_Image;
use ITRocks\Framework\Layout\Structure\Field\Final_Text;
use ITRocks\Framework\Layout\Structure\Field\Image;
use ITRocks\Framework\Layout\Structure\Field\Property;
use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Group\Iteration;
use ITRocks\Framework\Layout\Structure\Has_Structure;
use ITRocks\Framework\Layout\Structure\Page;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Property\Reflection_Property;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ReflectionException;

/**
 * Property to final text elements
 */
class Property_To_Text
{
	use Has_Structure { __construct as private structureConstruct; }

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

	//---------------------------------------------------------------------------------------- $print
	/**
	 * Is it a print model ? If true, will use @print_getter to translate values for print
	 *
	 * @var boolean
	 */
	protected $print = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $structure Structure|null
	 * @param $print     boolean
	 */
	public function __construct(Structure $structure = null, bool $print = false)
	{
		$this->structureConstruct($structure);
		$this->print = $print;
	}

	//---------------------------------------------------------------------------------------- append
	/**
	 * @param $final_element    Element|Final_Image|Final_Text
	 * @param $iteration_number integer
	 */
	protected function append(Element $final_element, int $iteration_number = null)
	{
		// append element to the group iteration / page
		if ($final_element->group) {
			$iteration                = $this->iteration($final_element->group, $iteration_number);
			$final_element->iteration = $iteration;
			$iteration->elements[]    = $final_element;
		}
		else {
			$final_element->page->elements[] = $final_element;
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
		if (reset($group->links) && strcmp(key($group->links), $group->page->number)) {
			return;
		}
		foreach ($group->groups as $sub_group) {
			$this->group($sub_group);
		}
		foreach ($group->elements as $element) {
			if (($element instanceof Image) || ($element instanceof Text)) {
				$this->groupElement($element);
			}
		}
		foreach ($group->properties as $property) {
			$this->property($property);
		}
	}

	//---------------------------------------------------------------------------------- groupElement
	/**
	 * Process an Image|Text element (always and uniquely from a group)
	 *
	 * @param $text Element|Image|Text
	 */
	protected function groupElement(Element $text)
	{
		$property_path = $text->group->property_path;
		$values        = $this->values($property_path);
		foreach ($values as $iteration_number => $object) {
			if ($text instanceof Text) {
				$parser = new Parser($object, $this->object, $text->group->property_path);
				$value  = $parser->elementText($text, $iteration_number);
			}
			else {
				$value = $text->file;
			}
			$final_text = $this->propertyToFinal($text, $value);
			$this->append($final_text, $iteration_number);
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
	protected function iteration(Group $group, int $iteration_number) : Iteration
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
	protected function nextObjects(array $objects, string $property_name) : array
	{
		$next_objects = [];
		if ($objects) {
			$iteration = 0;
			foreach ($objects as $object) {
				if (is_object($object)) {
					$reflection_property = new Reflection_Property($object, $property_name);
					break;
				}
			}
			if (!isset($reflection_property)) {
				return [];
			}
			foreach ($objects as $object) {
				/** @noinspection PhpUnhandledExceptionInspection must be valid here */
				/** @var $getter Method_Annotation */
				$object
					= ($this->print && ($getter = $reflection_property->getAnnotation('print_getter'))->value)
					? $getter->call($object)
					: $reflection_property->getValue($object);
				if (is_array($object)) {
					// TODO sub-arrays wont work at all : only one level of array values
					$next_objects = array_merge($next_objects, $object);
				}
				elseif (is_object($object) || strlen($object)) {
					$next_objects[$iteration] = $object;
				}
				else {
					$next_objects[$iteration] = null;
				}
				$iteration ++;
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
		foreach ($page->elements as $element_key => $element) {
			if (($element instanceof Text) && (strpos($element->text, '{') !== false)) {
				$this->pageText($element);
				unset($page->elements[$element_key]);
			}
		}
		foreach ($page->properties as $property) {
			$this->property($property);
		}
	}

	//-------------------------------------------------------------------------------------- pageText
	/**
	 * Process a Text element (always and uniquely from a page, and if contains {property.expression})
	 *
	 * @param $text Text
	 */
	protected function pageText(Text $text)
	{
		$parser     = new Parser($this->object);
		$value      = $parser->elementText($text);
		$final_text = $this->propertyToFinalText($text, $value);
		$this->append($final_text);
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
		/** @noinspection PhpUnhandledExceptionInspection valid object */
		$reflection_property = new Reflection_Property(
			get_class($this->object), $property->property_path
		);
		foreach ($this->values($property->property_path) as $iteration_number => $value) {
			$final_element = $this->propertyToFinal(
				$property, Loc::propertyToLocale($reflection_property, $value)
			);
			$this->append($final_element, $iteration_number);
		}
	}

	//------------------------------------------------------------------------------- propertyToFinal
	/**
	 * @param $property Field|Image|Property|Text
	 * @param $value    string
	 * @return Element|Final_Image|Final_Text
	 */
	protected function propertyToFinal(Field $property, string $value) : Element
	{
		return ($value instanceof File)
			? $this->propertyToFinalImage($property, $value)
			: $this->propertyToFinalText ($property, $value);
	}

	//-------------------------------------------------------------------------- propertyToFinalImage
	/**
	 * @param $property Field
	 * @param $value    File
	 * @return Final_Image
	 */
	protected function propertyToFinalImage(Field $property, File $value) : Final_Image
	{
		// change property to final image
		$final_image = new Final_Image($property->page);
		foreach (get_object_vars($property) as $property_name => $property_value) {
			if (property_exists($final_image, $property_name)) {
				$final_image->$property_name = $property_value;
			}
		}
		$final_image->file     = $value;
		$final_image->property = $property;
		return $final_image;
	}

	//--------------------------------------------------------------------------- propertyToFinalText
	/**
	 * @param $property Field|Property|Text
	 * @param $value    string
	 * @return Final_Text
	 */
	protected function propertyToFinalText(Field $property, string $value) : Final_Text
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
	 * @param $property_path string
	 * @return object[]
	 */
	protected function values(string $property_path) : array
	{
		// This is a 'linear' algorithm, not recursive, to go faster : objects list grow during descend
		$objects = [$this->object];
		foreach (explode(DOT, $property_path) as $property_name) {
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
