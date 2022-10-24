<?php
namespace ITRocks\Framework\Configuration\File\Builder;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Builder;
use ITRocks\Framework\Configuration\File\Writer\Namespace_White_Lines;

/**
 * Builder configuration file writer
 *
 * @override file @var Builder
 * @property Builder file
 */
class Writer extends File\Writer
{
	use Namespace_White_Lines;

	//---------------------------------------------------------------------------- writeConfiguration
	/**
	 * Write builder configuration to lines
	 */
	protected function writeConfiguration()
	{
		$this->initWhiteLine();
		$last_line_key = null;
		$this->lines[] = 'return [';
		foreach ($this->file->classes as $built_class) {
			if (is_object($built_class)) {
				if ($last_line_key) {
					$this->lines[$last_line_key] .= ',';
				}
				$short_class_name = $this->file->shortClassNameOf($built_class->class_name);
				$this->autoWhiteLine($short_class_name);
				if ($built_class instanceof Assembled) {
					$this->lines[] = TAB . $short_class_name . '::class'
						. ' => [';
					$component_count = count($built_class->components);
					foreach ($built_class->components as $component) {
						$component_count --;
						if (strStartsWith(trim($component), ['//', '/*'])) {
							$this->lines[] = $component;
							continue;
						}
						if (str_starts_with($component, AT)) {
							$component = Q . $component . Q;
						}
						$line = strStartsWith(trim($component), [DQ, Q])
							? $component
							: ($this->file->shortClassNameOf($component) . '::class');
						$this->lines[] = TAB . TAB . $line . ($component_count ? ',' : '');
					}
					$last_line_key = count($this->lines);
					$this->lines[] = TAB . ']';
				}
				elseif ($built_class instanceof Replaced) {
					$last_line_key = count($this->lines);
					$this->lines[] = TAB . $short_class_name . '::class'
						. ' => ' . $this->file->shortClassNameOf($built_class->replacement) . '::class';
				}
			}
			elseif (trim($built_class)) {
				$this->insert_lines[] = $built_class;
			}
		}
		$this->writeInsertLines();
		$this->lines[] = '];';
	}

}
