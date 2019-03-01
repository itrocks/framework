<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Configuration\File;

/**
 * Common code for all configuration file writers
 */
class Writer
{

	//-------------------------------------------- White lines after configuration sections constants
	const BEGIN_ENDS_WHITE         = true;
	const CONFIGURATION_ENDS_WHITE = true;

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var $file File
	 */
	protected $file;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * @var string[]
	 */
	protected $lines;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $file File
	 */
	public function __construct(File $file)
	{
		$this->file = $file;
	}

	//---------------------------------------------------------------------- removeMultipleWhiteLines
	/**
	 * Remove multiple white lines : only one is accepted
	 *
	 * @param $buffer string
	 */
	protected function removeMultipleWhiteLines(&$buffer)
	{
		while (strpos($buffer, LF . LF . LF)) {
			$buffer = str_replace(LF . LF . LF, LF . LF, $buffer);
		}
	}

	//-------------------------------------------------------------------------------------- usedUses
	/**
	 * Filter $this->file->use to keep only use clauses used into $buffer
	 *
	 * @param $buffer string
	 * @return string[]
	 */
	protected function usedUses($buffer)
	{
		$buffer_length = strlen($buffer);
		$used_uses     = [];
		foreach ($this->file->use as $use_key => $use) {
			$position    = 0;
			$short_name  = rLastParse($use, BS, 1, true);
			$name_length = strlen($short_name);
			while (($position !== false) && ($position < $buffer_length)) {
				$position = strpos($buffer, $short_name, $position);
				if ($position !== false) {
					$previous = substr($buffer, $position - 5, 5);
					if ($previous === (TAB . 'use' . SP)) {
						break;
					}
					$previous = $buffer[$position - 1];
					$next     = $buffer[$position + $name_length];
					if (
						!ctype_alnum($previous)
						&& ($previous !== BS)
						&& in_array($next, [BS, ':'], true)
					) {
						break;
					}
					$position ++;
				}
			}
			if ($position) {
				$used_uses[] = $use;
			}
		}
		return $used_uses;
	}

	//----------------------------------------------------------------------------------------- write
	public function write()
	{
		$this->lines = [];
		$this->writeBeginLines();
		$this->writeConfiguration();
		$this->writeEndLines();
		$buffer = $this->writeLines();
		$this->writeUse($buffer);
		$this->writeNamespace($buffer);
		$buffer = '<?php' . LF . $buffer;
		$this->removeMultipleWhiteLines($buffer);
		$this->writeBuffer($buffer);
	}

	//------------------------------------------------------------------------------- writeBeginLines
	/**
	 * Begin lines (between the last use clause and the configuration) into $this->lines[]
	 */
	protected function writeBeginLines()
	{
		if ($this->file->begin_lines) {
			$this->lines = array_merge($this->lines, $this->file->begin_lines);
			if (static::BEGIN_ENDS_WHITE) {
				$this->lines[] = '';
			}
		}
	}

	//----------------------------------------------------------------------------------- writeBuffer
	/**
	 * Write text buffer into file (if content changed)
	 *
	 * @param $write_buffer string
	 */
	protected function writeBuffer($write_buffer)
	{
		if (file_get_contents($this->file->file_name) !== $write_buffer) {
			script_put_contents($this->file->file_name, $write_buffer);
		}
	}

	//---------------------------------------------------------------------------- writeConfiguration
	/**
	 * Configuration into $this->lines[]
	 */
	protected function writeConfiguration()
	{
		if ($this instanceof Has_Configuration_Accessors) {
			$configuration_lines = $this->getConfigurationLines();
			if ($configuration_lines) {
				$this->lines   = array_merge($this->lines,  $configuration_lines);
				if (static::CONFIGURATION_ENDS_WHITE) {
					$this->lines[] = '';
				}
			}
		}
	}

	//--------------------------------------------------------------------------------- writeEndLines
	/**
	 * End lines (after configuration) into $this->lines[]
	 */
	protected function writeEndLines()
	{
		if ($this->file->end_lines) {
			$this->lines = array_merge($this->lines, $this->file->end_lines);
		}
	}

	//------------------------------------------------------------------------------------ writeLines
	/**
	 * Change $this->lines into string buffer
	 *
	 * @return string
	 */
	protected function writeLines()
	{
		return join(LF, $this->lines);
	}

	//-------------------------------------------------------------------------------- writeNamespace
	/**
	 * Prepend namespace at the beginning of the string buffer
	 *
	 * @param $buffer string
	 */
	protected function writeNamespace(&$buffer)
	{
		if ($this->file->namespace) {
			$buffer = 'namespace ' . $this->file->namespace . ';' . LF . LF . $buffer;
		}
	}

	//-------------------------------------------------------------------------------------- writeUse
	/**
	 * Prepend use clauses at the beginning of the string buffer
	 *
	 * @param $buffer string
	 */
	protected function writeUse(&$buffer)
	{
		if ($this->file->use) {
			$uses = [];
			foreach ($this->usedUses($buffer) as $use) {
				$uses[] = 'use ' . $use . ';';
			}
			$buffer = join(LF, $uses) . LF . LF . $buffer;
		}
	}

}
