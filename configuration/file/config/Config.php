<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework;
use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Config\Priority;
use ITRocks\Framework\Plugin;

/**
 * The menu.php configuration file
 */
class Config extends File
{

	//-------------------------------------------------------------------------- $plugins_by_priority
	/**
	 * @var Priority[]|string[]
	 */
	public array $plugins_by_priority;

	//----------------------------------------------------------------------------------- $start_line
	/**
	 * @var string
	 */
	public string $start_line;

	//------------------------------------------------------------------------------------- addPlugin
	/**
	 * @param $priority_value string @values Framework\Plugin\Priority::const
	 * @param $plugin_name    string plugin class name
	 * @param $configuration  mixed
	 * @see Framework\Plugin\Priority::const
	 */
	public function addPlugin(string $priority_value, string $plugin_name, mixed $configuration)
		: void
	{
		$priority = $this->addPriority($priority_value);
		$priority->addPlugin($plugin_name, $configuration);
		$this->addUseFor($plugin_name, 2);
	}

	//----------------------------------------------------------------------------------- addPriority
	/**
	 * Add a priority or return the existing priority
	 *
	 * @param $priority_value string
	 * @return Priority
	 */
	public function addPriority(string $priority_value) : Priority
	{
		$priority = $this->searchPriority($priority_value);
		if (!$priority) {
			$ordered_priorities = Plugin\Priority::orderedPriorities();
			$new_position       = array_search($priority_value, $ordered_priorities);
			$insert_position    = count($this->plugins_by_priority);
			foreach ($this->plugins_by_priority as $position => $priority) {
				if (
					!($priority instanceof Priority)
					|| (array_search($priority->priority, $ordered_priorities) < $new_position)
				) {
					continue;
				}
				$insert_position = ($position - 1);
				break;
			}
			$priority         = new Priority($priority_value);
			$priority->config = $this;
			$line             = TAB . '//' . str_repeat('-', 77 - strlen($priority_value));
			$new_lines        = [
				$line . SP . strtoupper($priority_value) . SP . 'priority plugins', $priority, ''
			];
			$this->plugins_by_priority = array_merge(
				array_slice($this->plugins_by_priority, 0, $insert_position),
				$new_lines,
				array_slice($this->plugins_by_priority, $insert_position)
			);
		}
		return $priority;
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read from file
	 */
	public function read() : void
	{
		(new Config\Reader($this))->read();
	}

	//---------------------------------------------------------------------------------- removePlugin
	/**
	 * @param $plugin_name string
	 */
	public function removePlugin(string $plugin_name) : void
	{
		$recalculate_keys = false;
		foreach ($this->plugins_by_priority as $key => $priority) {
			if ($priority instanceof Priority) {
				if (
					$priority->removePlugin($plugin_name)
					&& $priority->emptyIfNoPluginAnymore()
				) {
					unset($this->plugins_by_priority[$key]);
					$remove_key = $key - 1;
					while (
						$remove_key
						&& isset($this->plugins_by_priority[$remove_key])
						&& !($this->plugins_by_priority[$remove_key] instanceof Priority)
						&& (
							($this->plugins_by_priority[$remove_key] === '')
							|| str_starts_with($this->plugins_by_priority[$remove_key], TAB . '//')
						)
					) {
						unset($this->plugins_by_priority[$remove_key]);
						$remove_key --;
					}
					$recalculate_keys = true;
				}
			}
		}
		if ($recalculate_keys) {
			$this->plugins_by_priority = array_values($this->plugins_by_priority);
		}
	}

	//-------------------------------------------------------------------------------- searchPriority
	/**
	 * Search a priority
	 *
	 * @param $priority_value string
	 * @return ?Config\Priority
	 */
	public function searchPriority(string $priority_value) : ?Config\Priority
	{
		foreach ($this->plugins_by_priority as $priority) {
			if (($priority instanceof Priority) && ($priority->priority === $priority_value)) {
				return $priority;
			}
		}
		return null;
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write to file
	 */
	public function write() : void
	{
		(new Config\Writer($this))->write();
	}

}
