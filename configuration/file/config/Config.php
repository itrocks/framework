<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework;
use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Config\Priority;

/**
 * The menu.php configuration file
 */
class Config extends File
{

	//-------------------------------------------------------------------------- $plugins_by_priority
	/**
	 * @var Priority[]|string[]
	 */
	public $plugins_by_priority;

	//----------------------------------------------------------------------------------- $start_line
	/**
	 * @var string
	 */
	public $start_line;

	//------------------------------------------------------------------------------------- addPlugin
	/**
	 * @param $priority_value string @values Framework\Plugin\Priority::const
	 * @param $plugin_name    string plugin class name
	 * @param $configuration  mixed
	 * @see Framework\Plugin\Priority::const
	 */
	public function addPlugin($priority_value, $plugin_name, $configuration)
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
	public function addPriority($priority_value)
	{
		$priority = $this->searchPriority($priority_value);
		if (!$priority) {
			$priority                    = new Priority($priority_value);
			$priority->config            = $this;
			$this->plugins_by_priority[] = '';
			$line                        = TAB . '//' . str_repeat('-', 77 - strlen($priority_value));
			$this->plugins_by_priority[] = $line . SP
				. strtoupper($priority_value) . SP . 'priority plugins';
			$this->plugins_by_priority[] = $priority;
			$this->plugins_by_priority[] = '';
		}
		return $priority;
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read from file
	 */
	public function read()
	{
		(new Config\Reader($this))->read();
	}

	//---------------------------------------------------------------------------------- removePlugin
	/**
	 * @param $plugin_name string
	 */
	public function removePlugin($plugin_name)
	{
		foreach ($this->plugins_by_priority as $priority) {
			if ($priority instanceof Priority) {
				$priority->removePlugin($plugin_name);
			}
		}
	}

	//-------------------------------------------------------------------------------- searchPriority
	/**
	 * Search a priority
	 *
	 * @param $priority_value string
	 * @return Config\Priority|null
	 */
	public function searchPriority($priority_value)
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
	public function write()
	{
		(new Config\Writer($this))->write();
	}

}
