<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Has_Structure;

/**
 * Some free texts may contain some {property.path} (VO / translated) : change them to data
 */
class Text_Templating
{
	use Has_Structure;

	//--------------------------------------------------------------------------- PAGE_PROPERTY_PATHS
	const PAGE_PROPERTY_PATHS = ['page.number', 'pages.count'];

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
	 * Parse final data
	 */
	public function run()
	{
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
	protected function text(Text $element)
	{
		foreach (static::PAGE_PROPERTY_PATHS as $property_path) {
			$search = '{' . $property_path . '}';
			if (strpos($element->text, $search) !== false) {
				$element->text = str_replace(
					$search, $this->pageProperty($property_path, $element), $element->text
				);
			}
		}
	}

}
