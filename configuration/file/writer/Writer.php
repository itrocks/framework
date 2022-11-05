<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Configuration\File;

/**
 * Common code for all configuration file writers
 */
abstract class Writer
{

	//-------------------------------------------- White lines after configuration sections constants
	const BEGIN_ENDS_WHITE         = true;
	const CONFIGURATION_ENDS_WHITE = true;

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var $file File
	 */
	protected File $file;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * @var string[]
	 */
	protected array $lines;

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
	protected function removeMultipleWhiteLines(string &$buffer) : void
	{
		while (str_contains($buffer, LF . LF . LF)) {
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
	protected function usedUses(string $buffer) : array
	{
		$ignore_next_class = false;
		$in_class          = false;
		$unused_uses = $used_uses = [];
		foreach ($this->file->use as $use) {
			$short_use = rLastParse($use, BS, 1, true);
			$unused_uses[$short_use] = $used_uses[$short_use] = $use;
		}
		foreach (token_get_all('<?php ' . $buffer) as $token) {
			$token_id = $token[0];
			if (($token_id === T_USE) && !$in_class) {
				$ignore_next_class = true;
			}
			elseif ($token_id === T_NAME_QUALIFIED) {
				$class_name = lParse($token[1], BS);
				if ($ignore_next_class) {
					$ignore_next_class = false;
				}
				elseif (isset($unused_uses[$class_name])) {
					unset($unused_uses[$class_name]);
					if (!$unused_uses) {
						break;
					}
				}
			}
			elseif (in_array($token_id, [T_CLASS, T_INTERFACE, T_TRAIT])) {
				$in_class = true;
			}
		}
		foreach (array_keys($unused_uses) as $short_use) {
			unset($used_uses[$short_use]);
		}
		return array_values($used_uses);
	}

	//----------------------------------------------------------------------------------------- write
	public function write() : void
	{
		$this->lines = [];
		$this->writeBeginLines();
		$this->writeConfiguration();
		$this->writeEndLines();
		$buffer = $this->writeLines();
		$this->writeUse($buffer);
		$this->writeNamespace($buffer);
		$buffer = '<?php' . LF . $buffer;
		if (static::CONFIGURATION_ENDS_WHITE) {
			$buffer .= LF;
		}
		$this->removeMultipleWhiteLines($buffer);
		$this->writeBuffer($buffer);
	}

	//------------------------------------------------------------------------------- writeBeginLines
	/**
	 * Begin lines (between the last use clause and the configuration) into $this->lines[]
	 */
	protected function writeBeginLines() : void
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
	protected function writeBuffer(string $write_buffer) : void
	{
		if (file_get_contents($this->file->file_name) !== $write_buffer) {
			script_put_contents($this->file->file_name, $write_buffer);
		}
	}

	//---------------------------------------------------------------------------- writeConfiguration
	/**
	 * Configuration into $this->lines[]
	 */
	abstract protected function writeConfiguration() : void;

	//--------------------------------------------------------------------------------- writeEndLines
	/**
	 * End lines (after configuration) into $this->lines[]
	 */
	protected function writeEndLines() : void
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
	protected function writeLines() : string
	{
		return join(LF, $this->lines);
	}

	//-------------------------------------------------------------------------------- writeNamespace
	/**
	 * Prepend namespace at the beginning of the string buffer
	 *
	 * @param $buffer string
	 */
	protected function writeNamespace(string &$buffer) : void
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
	protected function writeUse(string &$buffer) : void
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
