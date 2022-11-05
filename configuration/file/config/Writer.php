<?php
namespace ITRocks\Framework\Configuration\File\Config;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Config;
use ITRocks\Framework\Configuration\File\Writer\Namespace_White_Lines;

/**
 * Config configuration file writer
 *
 * @override file @var Config
 * @property Config file
 */
class Writer extends File\Writer
{
	use Namespace_White_Lines;

	//--------------------------------------------------------------------------------- lastObjectKey
	/**
	 * @param $elements object[]|string[]
	 * @return integer
	 */
	protected function lastObjectKey(array $elements) : int
	{
		end($elements);
		while (key($elements) && !is_object(current($elements))) {
			prev($elements);
		}
		return key($elements);
	}

	//---------------------------------------------------------------------------- writeConfiguration
	/**
	 * Write builder configuration to lines
	 */
	protected function writeConfiguration() : void
	{
		$last_priority_key = $this->lastObjectKey($this->file->plugins_by_priority);
		$this->lines[]     = $this->file->start_line;
		foreach ($this->file->plugins_by_priority as $priority_key => $priority) {
			if ($priority instanceof Priority) {
				$this->initWhiteLine();
				$last_plugin_key = $this->lastObjectKey($priority->plugins);
				$this->lines[]   = TAB . 'Priority::' . strtoupper($priority->priority) . ' => [';
				foreach ($priority->plugins as $plugin_key => $plugin) {
					if ($plugin instanceof Plugin) {
						$plugins_separator = ($plugin_key === $last_plugin_key) ? '' : ',';
						$short_class_name  = $this->file->shortClassNameOf($plugin->class_name, 2);
						$this->autoWhiteLine($short_class_name);
						$this->lines[] = TAB . TAB
							. $short_class_name . '::class'
							. ($plugin->configuration ? (' => ' . $plugin->configuration) : '')
							. $plugins_separator;
					}
					elseif (trim($plugin)) {
						$this->insert_lines[] = $plugin;
					}
				}
				$this->writeInsertLines();
				$priority_separator = ($priority_key === $last_priority_key) ? '' : ',';
				$this->lines[] = TAB . ']'. $priority_separator;
			}
			else {
				$this->lines[] = $priority;
			}
		}
		$this->lines[] = '];';
	}

}
