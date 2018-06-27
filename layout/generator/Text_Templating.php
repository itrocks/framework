<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Has_Structure;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Reflection_Property;
use ReflectionException;

/**
 * Some free texts may contain some {property.path} (VO / translated) : change them to data
 */
class Text_Templating
{
	use Has_Structure;

	//--------------------------------------------------------------------------- PAGE_PROPERTY_PATHS
	const PAGE_PROPERTY_PATHS = ['page.number', 'pages.count'];

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	public $object;

	//-------------------------------------------------------------------------------- objectProperty
	/**
	 * @param $property_path string
	 * @return mixed
	 */
	protected function objectProperty($property_path)
	{
		try {
			$property = new Reflection_Property(get_class($this->object), $property_path);
			$value    = $property->getValue($this->object);
		}
		// if the property.path does not match : keep {property.path} unmodified
		catch (ReflectionException $exception) {
			return '{' . $property_path . '}';
		}
		return $value;
	}

	//---------------------------------------------------------------------------------- pageProperty
	/**
	 * @param $property_path string
	 * @param $element       Text
	 * @return mixed
	 */
	protected function pageProperty($property_path, Text $element)
	{
		switch ($property_path) {
			case 'page.number': return $element->page->number;
			case 'pages.count': return count($this->structure->pages);
		}
		return null;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $object object
	 */
	public function run($object)
	{
		$this->object = $object;
		foreach ($this->structure->pages as $page) {
			foreach ($page->elements as $element) {
				if (($element instanceof Text) && (strpos($element->text, '{') !== false)) {
					$this->text($element);
				}
			}
			foreach ($page->groups as $group) {
				foreach ($group->iterations as $iteration) {
					foreach ($iteration->elements as $element) {
						if (($element instanceof Text) && (strpos($element->text, '{') !== false)) {
							$this->text($element);
						}
					}
				}
			}
		}
	}

	//------------------------------------------------------------------------------------------ text
	/**
	 * @param $element Text
	 */
	public function text(Text $element)
	{
		$text     = $element->text;
		$position = 0;
		while (($position = strpos($text, '{', $position)) !== false) {
			$position      ++;
			$end_position  = strpos($text, '}', $position);
			$property_path = Loc::rtr(substr($text, $position, $end_position - $position));
			$value         = in_array($property_path, static::PAGE_PROPERTY_PATHS)
				? $this->pageProperty($property_path, $element)
				: $this->objectProperty($property_path);
			$text      = substr($text, 0, $position - 1) . $value . substr($text, $end_position + 1);
			$position += strlen($value) - 1;
		}
		$element->text = $text;
	}

}
