<?php
namespace ITRocks\Framework\Layout\Structure\Field\Text;

use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Tools\Names;

/**
 * Text templating trait
 */
#[Extend(Text::class)]
trait Templating
{

	//------------------------------------------------------------------------------- $property_paths
	/**
	 * Property paths cache
	 *
	 * @var string[]
	 */
	private array $property_paths;

	//--------------------------------------------------------------------------------- propertyPaths
	/**
	 * Gets only property.paths ("constant texts" are ignored)
	 *
	 * @return string[]
	 */
	public function propertyPaths() : array
	{
		if (isset($this->property_paths)) {
			return $this->property_paths;
		}
		if (!str_contains($this->text, '{')) {
			return $this->property_paths = [];
		}
		$this->property_paths = [];
		$template_sections    = explode('{', $this->text);
		array_shift($template_sections);
		foreach ($template_sections as $template_section) {
			$property_paths = explode('?:', substr($template_section, 0, strpos($template_section, '}')));
			foreach ($property_paths as $property_path) {
				if (
					in_array(substr($property_path, 0, 1), [DQ, Q])
					&& (substr($property_path, -1) === $property_path[0])
				) {
					continue;
				}
				$this->property_paths[] = Names::displayToProperty(str_replace('?', '', $property_path));
			}
		}
		return $this->property_paths;
	}

}
