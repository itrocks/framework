<?php
namespace ITRocks\Framework\Configuration\File\Menu;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Menu;
use ITRocks\Framework\Widget\Menu\Block;
use ITRocks\Framework\Widget\Menu\Item;

/**
 * Builder configuration file reader
 *
 * @override file @var Menu
 * @property Menu file
 */
class Reader extends File\Reader
{

	//----------------------------------------------------------------------------- readConfiguration
	/**
	 * Read configuration : the main part of the file
	 */
	protected function readConfiguration()
	{
		$block = null;
		$line  = current($this->lines);
		while (!trim($line)) {
			$line = next($this->lines);
		}
		for ($ended = false; !$ended; $line = next($this->lines)) {
			if ($this->isEndLine($line)) {
				$ended = true;
			}
			else {
				// menu item level
				if (beginsWith($line, TAB . TAB)) {
					if ($block instanceof Block) {
						if (beginsWith(trim($line), ['//', '/*']) || !trim($line)) {
							$block->items[] = $line;
						}
						else {
							$item           = new Item();
							$item->caption  = trim(trim(rParse($line, '=>')), Q . DQ . ',');
							$item->link     = trim(trim(lParse($line, '=>')), Q . DQ);
							$block->items[] = $item;
						}
					}
					else {
						trigger_error(
							'Only ' . Block::class . ' can accept items,'
							. ' bad menu item description ' . Q . $line . Q
							. ' into file ' . $this->file->file_name . ' line ' . (key($this->lines) + 1),
							E_USER_ERROR
						);
					}
				}
				// menu block level
				elseif (beginsWith($line, TAB)) {
					if (beginsWith(trim($line), ['//', '/*']) || !trim($line)) {
						$this->file->blocks[] = $line;
					}
					elseif (in_array(trim($line), [']', '],'])) {
						$block = null;
					}
					else {
						$title = trim(trim(lParse($line, '=>')), Q . DQ);
						// 'Menu block title' => [
						if (strpos($line, '=>') && strpos($line, '[')) {
							$block                = new Block();
							$block->title         = $title;
							$this->file->blocks[] = $block;
							// '/Full/Class/Path' => [Menu::ALL => Menu::CLEAR]
							if (strpos($line, ']')) {
								$block->items[] = mParse($line, '[', ']');
								$block          = null;
							}
						}
						else {
							trigger_error(
								' bad menu block description ' . Q . $line . Q
								. ' into file ' . $this->file->file_name . ' line ' . (key($this->lines) + 1),
								E_USER_ERROR
							);
						}
					}
				}
				elseif ($block instanceof Block) {
					$block->items[] = $line;
				}
				else {
					$this->file->blocks[] = $line;
				}
			}
		}
	}

}
