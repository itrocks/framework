<?php
namespace ITRocks\Framework\Configuration\File\Menu;

use ITRocks\Framework\Component\Menu\Block;
use ITRocks\Framework\Component\Menu\Item;
use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Menu;

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
			elseif ($block->title === 'Menu::TITLE') {
				if ($last_line_key) {
					$this->lines[$last_line_key] .= ',';
				}
				$last_line_key = count($this->lines);
				$this->lines[] = TAB . $block->title . ' => [' . $block->items[0] . ']';
			}
			else {
				if ($last_line_key) {
					$this->lines[$last_line_key] .= ',';
				}
				if ($block instanceof Block) {
					$component_count = count($block->items);
					if (($component_count === 1) && (reset($block->items) === 'Menu::ALL => Menu::CLEAR')) {
						$last_line_key = count($this->lines);
						$this->lines[] = TAB . Q . str_replace(Q, BS . Q, $block->title) . Q
							. ' => [Menu::ALL => Menu::CLEAR]';
					}
					else {
						$this->lines[] = TAB . Q . str_replace(Q, BS . Q, $block->title) . Q . ' => [';
						foreach ($block->items as $item) {
							$component_count --;
							if ($item instanceof Item) {
								if (($item->caption === 'Menu::CLEAR') && ($item->link === 'Menu::ALL')) {
									$this->lines[] = TAB . TAB . 'Menu::ALL => Menu::CLEAR'
										. ($component_count ? ',' : '');
								}
								else {
									$this->lines[] = TAB . TAB . Q . $item->link . Q
										. ' => ' . Q . str_replace(Q, BS . Q, $item->caption) . Q
										. ($component_count ? ',' : '');
								}
							}
							else {
								$this->lines[] = $item;
							}
						}
						$last_line_key = count($this->lines);
						$this->lines[] = TAB . ']';
					}
				}
				else {
					$last_line_key = null;
					$this->lines[] = $block;
				}
			}
		}
		$this->lines[] = '];';
		$this->lines[] = '';
	}

}
