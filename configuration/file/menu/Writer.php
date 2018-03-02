<?php
namespace ITRocks\Framework\Configuration\File\Menu;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Menu;
use ITRocks\Framework\Widget\Menu\Block;

/**
 * Builder configuration file writer
 *
 * @override file @var Menu
 * @property Menu file
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
		foreach ($this->file->blocks as $block) {
			if (is_string($block)) {
				$this->lines[] = $block;
			}
			else {
				if ($last_line_key) {
					$this->lines[$last_line_key] .= ',';
				}
				if ($block instanceof Block) {
					$this->lines[] = TAB . ''; // TODO
					$component_count = count($block->items);
					foreach ($block->items as $item) {
						$component_count --;
						$this->lines[] = TAB . TAB . '' // TODO
							. ($component_count ? ',' : '');
					}
					$last_line_key = count($this->lines);
					$this->lines[] = TAB . ']';
				}
			}
		}
		$this->lines[] = '];';
		$this->lines[] = '';
	}

}
