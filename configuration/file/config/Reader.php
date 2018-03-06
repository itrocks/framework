<?php
namespace ITRocks\Framework\Configuration\File\Config;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Config;

/**
 * Builder configuration file reader
 *
 * @override file @var Config
 * @property Config file
 */
class Reader extends File\Reader
{

	//----------------------------------------------------------------------------------- isStartLine
	/**
	 * @param $line string
	 * @return boolean
	 */
	public function isStartLine($line)
	{
		return beginsWith($line, '$config[') && endsWith($line, '] = [');
	}

	//----------------------------------------------------------------------------- readConfiguration
	/**
	 * Read configuration : the main part of the file
	 */
	protected function readConfiguration()
	{
		$line = current($this->lines);
		/** @var $plugin Plugin */
		$plugin = null;
		/** @var $priority Priority */
		$priority = null;
		while (!trim($line)) {
			$line = next($this->lines);
		}
		for ($ended = false; !$ended; $line = next($this->lines)) {
			if ($this->isEndLine($line)) {
				$ended = true;
			}
			// comment line
			elseif (beginsWith(trim($line), ['//', '/*']) || !trim($line)) {
				if ($plugin) {
					$plugin->configuration .= LF . $line;
				}
				elseif ($priority) {
					$priority->plugins[] = $line;
				}
				else {
					$this->file->plugins_by_priority[] = $line;
				}
			}
			else {
				// plugin configuration flow level
				if (beginsWith($line, TAB . TAB . TAB)) {
					if ($plugin instanceof Plugin) {
						$plugin->configuration .= LF . $line;
					}
					else {
						trigger_error(
							'Only ' . Plugin::class . ' can accept plugin configuration lines,'
							. ' bad plugin configuration ' . Q . $line . Q
							. ' into file ' . $this->file->file_name . ' line ' . (key($this->lines) + 1),
							E_USER_ERROR
						);
					}
				}
				// plugin configuration begin level
				if (beginsWith($line, TAB . TAB)) {
					if (in_array(trim($line), [']', '],'])) {
						$plugin = null;
					}
					elseif ($priority instanceof Priority) {
						$plugin                = new Plugin();
						$plugin->class_name    = $this->fullClassNameOf(lParse($line, '=>'));
						$plugin->configuration = trim(rParse($line, '=>'));
						$priority->plugins[]   = $plugin;
					}
					else {
						trigger_error(
							'Only ' . Priority::class . ' can accept plugins,'
							. ' bad plugin configuration ' . Q . $line . Q
							. ' into file ' . $this->file->file_name . ' line ' . (key($this->lines) + 1),
							E_USER_ERROR
						);
					}
				}
				// priority level
				elseif (beginsWith($line, TAB)) {
					if (in_array(trim($line), [']', '],'])) {
						$priority = null;
						$plugin   = null;
					}
					elseif (trim(lParse($line, '::')) === 'Priority') {
						$priority = new Priority(trim(mParse($line, 'Priority::', '=>')));
						$this->file->plugins_by_priority[] = $priority;
					}
					elseif (trim(lParse($line, '::')) === 'Configuration') {
						$this->file->plugins_by_priority[] = $line;
					}
					else {
						trigger_error(
							'Bad configuration line ' . Q . $line . Q
							. ' into file ' . $this->file->file_name . ' line ' . (key($this->lines) + 1),
							E_USER_ERROR
						);
					}
				}
				else {
					trigger_error(
						'Bad configuration line ' . Q . $line . Q
						. ' into file ' . $this->file->file_name . ' line ' . (key($this->lines) + 1),
						E_USER_ERROR
					);
				}
			}
		}
	}

}
