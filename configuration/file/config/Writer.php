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
				$this->lines[]    = TAB . 'Priority::' . strtoupper($priority->priority) . ' => [';
				$plugins          = $priority->plugins;
				$insert_lines     = [];
				$last_plugin_key  = $this->lastObjectKey($plugins);
				$last_namespace   = '';
				foreach ($plugins as $plugin_key => $plugin) {
					if ($plugin instanceof Plugin) {
						$plugins_separator = ($plugin_key === $last_plugin_key) ? '' : ',';
						$short_class_name  = $this->file->shortClassNameOf($plugin->class_name, 2);
						$namespace         = lParse($short_class_name, BS);
						if ($last_namespace !== $namespace) {
							if ($last_namespace) {
								$this->lines[] = '';
							}
							foreach ($insert_lines as $line) {
								$this->lines[] = $line;
							}
							$insert_lines   = [];
							$last_namespace = $namespace;
						}
						$this->lines[] = TAB . TAB
							. $short_class_name . '::class'
							. ($plugin->configuration ? (' => ' . $plugin->configuration) : '')
							. $plugins_separator;
					}
					elseif (trim($plugin)) {
						$insert_lines[] = $plugin;
					}
				}
				foreach ($insert_lines as $line) {
					$this->lines[] = $line;
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
