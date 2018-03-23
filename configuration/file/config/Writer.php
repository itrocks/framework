<?php
namespace ITRocks\Framework\Configuration\File\Config;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Config;

/**
 * Builder configuration file writer
 *
 * @override file @var Config
 * @property Config file
 */
class Writer extends File\Writer
{

	//--------------------------------------------------------------------------------- lastObjectKey
	/**
	 * @param $elements object[]|string[]
	 * @return integer
	 */
	protected function lastObjectKey(array $elements)
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
	protected function writeConfiguration()
	{
		$last_priority_key = $this->lastObjectKey($this->file->plugins_by_priority);
		$this->lines[]     = $this->file->start_line;
		foreach ($this->file->plugins_by_priority as $priority_key => $priority) {
			if ($priority instanceof Priority) {
				$this->lines[]   = TAB . 'Priority::' . strtoupper($priority->priority) . ' => [';
				$plugins         = $priority->plugins;
				$last_plugin_key = $this->lastObjectKey($plugins);
				foreach ($plugins as $plugin_key => $plugin) {
					if ($plugin instanceof Plugin) {
						$plugins_separator = ($plugin_key === $last_plugin_key) ? '' : ',';
						$this->lines[] = TAB . TAB . $this->shortClassNameOf($plugin->class_name, 2) . '::class'
							. ($plugin->configuration ? (' => ' . $plugin->configuration) : '')
							. $plugins_separator;
					}
					else {
						$this->lines[] = $plugin;
					}
				}
				$priority_separator = ($priority_key === $last_priority_key) ? '' : ',';
				$this->lines[] = TAB . ']'. $priority_separator;
			}
			else {
				$this->lines[] = $priority;
			}
		}
		$this->lines[] = '];';
		$this->lines[] = '';
	}

}
