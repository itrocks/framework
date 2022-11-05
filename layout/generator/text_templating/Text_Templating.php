<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Has_Structure;
use ITRocks\Framework\Tools\Names;

/**
 * Some free texts may contain some {property.path} (VO / translated) : change them to data
 */
class Text_Templating
{
	use Has_Structure;

	//--------------------------------------------------------------------------- PAGE_PROPERTY_PATHS
	const PAGE_PROPERTY_PATHS = ['page.number', 'pages.count'];

	//----------------------------------------------------------------------------- PAGE_SIMPLE_PATHS
	const PAGE_SIMPLE_PATHS   = [
		'#'    => '{page.number}',
		'##'   => '{pages.count}',
		'#/#'  => '{page.number}/{pages.count}',
		'#/##' => '{page.number}/{pages.count}'
	];

	//---------------------------------------------------------------------------------- pageProperty
	/**
	 * @param $property_path string
	 * @param $element       Text
	 * @return int|string|null
	 */
	protected function pageProperty(string $property_path, Text $element) : int|string|null
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
	public function run() : void
	{
		foreach ($this->structure->pages as $page) {
			foreach ($page->elements as $element) {
				if (
					($element instanceof Text)
					&& (str_contains($element->text, '{') || str_starts_with($element->text, '#'))
				) {
					$this->text($element);
				}
			}
			foreach ($page->groups as $group) {
				foreach ($group->iterations as $iteration) {
					foreach ($iteration->elements as $element) {
						if (
							($element instanceof Text)
							&& (str_contains($element->text, '{') || str_starts_with($element->text, '#'))
						) {
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
	protected function text(Text $element) : void
	{
		if (str_starts_with($element->text, '#')) {
			foreach (static::PAGE_SIMPLE_PATHS as $simple_path => $extended_path) {
				if ($element->text === $simple_path) {
					$element->text = $extended_path;
				}
			}
		}
		foreach (static::PAGE_PROPERTY_PATHS as $property_path) {
			$search = '{' . Names::propertyToDisplay($property_path) . '}';
			if (str_contains($element->text, $search)) {
				$element->text = str_replace(
					$search, $this->pageProperty($property_path, $element), $element->text
				);
			}
		}
	}

}
