<?php
namespace ITRocks\Framework\Layout\Structure\Page;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Layout\Structure\Draw\Snap_Line;
use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Element\Has_Init;
use ITRocks\Framework\Layout\Structure\Field\Property;
use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Page;

/**
 * Builds a structure from JSON data
 */
class From_Json
{

	//-------------------------------------------------------------------------------------- $builder
	/**
	 * Builder associations (sorted : stop once the matching has been found)
	 *
	 * @var array string[$class_name string][string integer|string]
	 */
	public $builder = [
		Property\Resizable::class => ['field', 'format' => 'text-cr'],
		Property::class           => ['field'],
		Snap_Line::class          => ['class' => 'snap'],
		Text\Resizable::class     => ['text', 'format' => 'text-cr'],
		Text::class               => ['text']
	];

	//--------------------------------------------------------------------------------------- $ignore
	/**
	 * No warning for element that embed these classes : these elements are simply ignored
	 *
	 * @var string[]
	 */
	public $ignore = ['snap'];

	//------------------------------------------------------------------------------------ $translate
	/**
	 * Translate field names into the source json structure into property names into destination
	 * Element objects
	 *
	 * @var string[] $field_name => $property_name
	 */
	public $translate = [
		'field' => 'property_path'
	];

	//----------------------------------------------------------------------------------------- build
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $json string
	 * @return Page
	 */
	public function build($json)
	{
		$page = new Page();
		foreach (json_decode($json) as $raw_element) {
			if (is_object($raw_element)) {
				$raw_element = get_object_vars($raw_element);
			}
			$classes = explode(SP, $raw_element['class']);
			$element = null;
			foreach ($this->builder as $element_class_name => $builder) {
				$found = false;
				foreach ($builder as $key => $value) {
					// all classes (separated by spaces) must be into the raw element
					if ($key === 'class') {
						$values    = explode(SP, $value);
						$intersect = array_intersect($classes, $values);
						$found     = (count($intersect) === count($values));
					}
					// the element attribute must exist and have this value
					elseif (is_string($key)) {
						$found = isset($raw_element[$key]) && ($raw_element[$key] === $value);
					}
					// the element must have this attribute
					else {
						$found = isset($raw_element[$value]);
					}
					// not found : resume to the next $builder item
					if (!$found) {
						break;
					}
				}
				// found : instantiate $element and stop search
				if ($found) {
					/** @noinspection PhpUnhandledExceptionInspection $class_name comes from ::class */
					/** @var $element Element */
					$element = Builder::create($element_class_name, [$page]);
					$this->buildElement($element, $raw_element);
					break;
				}
			}
			// no element class found : warning (element will be ignored)
			if ($element) {
				$page->elements[] = $element;
			}
			elseif (!array_intersect($classes, $this->ignore)) {
				trigger_error('No build method for ' . print_r($raw_element, true), E_USER_WARNING);
			}
		}
		return $page;
	}

	//---------------------------------------------------------------------------------- buildElement
	/**
	 * @param $element     Element
	 * @param $raw_element string[]
	 */
	protected function buildElement(Element $element, array $raw_element)
	{
		$class_name = get_class($element);
		foreach ($raw_element as $field_name => $value) {
			$property_name = isset($this->translate[$field_name])
				? $this->translate[$field_name]
				: $field_name;
			if (property_exists($class_name, $property_name)) {
				$element->$property_name = $value;
			}
		}
		if ($element instanceof Has_Init) {
			$element->init();
		}
	}

}
