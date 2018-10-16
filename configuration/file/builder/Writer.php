<?php
namespace ITRocks\Framework\Configuration\File\Builder;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Builder;

/**
 * Builder configuration file writer
 *
 * @override file @var Builder
 * @property Builder file
 */
class Writer extends File\Writer
{

	//---------------------------------------------------------------------------- writeConfiguration
	/**
	 * Write builder configuration to lines
	 */
	protected function writeConfiguration()
	{
		$this->lines[] = 'return [';
		$last_line_key = null;
		foreach ($this->file->classes as $built_class) {
			if (is_string($built_class)) {
				$this->lines[] = $built_class;
			}
			else {
				if ($last_line_key) {
					$this->lines[$last_line_key] .= ',';
				}
				if ($built_class instanceof Assembled) {
					$this->lines[] = TAB . $this->file->shortClassNameOf($built_class->class_name) . '::class'
						. ' => [';
					$component_count = count($built_class->components);
					foreach ($built_class->components as $component) {
						$component_count --;
						$line = beginsWith($component, [DQ, Q])
							? $component
							: ($this->file->shortClassNameOf($component) . '::class');
						$this->lines[] = TAB . TAB . $line . ($component_count ? ',' : '');
					}
					$last_line_key = count($this->lines);
					$this->lines[] = TAB . ']';
				}
				elseif ($built_class instanceof Replaced) {
					$last_line_key = count($this->lines);
					$this->lines[] = TAB . $this->file->shortClassNameOf($built_class->class_name) . '::class'
						. ' => ' . $this->file->shortClassNameOf($built_class->replacement) . '::class';
				}
			}
		}
		$this->lines[] = '];';
		$this->lines[] = '';
	}

}
