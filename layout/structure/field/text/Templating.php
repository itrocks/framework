<?php
namespace ITRocks\Framework\Layout\Structure\Field\Text;

use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Tools\Names;

/**
 * Text templating trait
 *
 * @extends Text
 * @see Text
 */
trait Templating
{

	//------------------------------------------------------------------------------- $property_paths
	/**
	 * Property paths cache
	 *
	 * @var string[]
	 */
	private $property_paths;

	//--------------------------------------------------------------------------------- propertyPaths
	/**
	 * @return string[]
	 */
	public function propertyPaths()
	{
		if (isset($this->property_paths)) {
			return $this->property_paths;
		}
		if (strpos($this->text, '{') === false) {
			return $this->property_paths = [];
		}
		$this->property_paths = [];
		$template_sections    = explode('{', $this->text);
		array_shift($template_sections);
		foreach ($template_sections as $template_section) {
			$property_paths = explode('?:', substr($template_section, 0, strpos($template_section, '}')));
			foreach ($property_paths as $property_path) {
				$this->property_paths[] = Names::displayToProperty(str_replace('?', '', $property_path));
			}
		}
		return $this->property_paths;
	}

}
