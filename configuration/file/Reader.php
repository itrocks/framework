<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Configuration\File;

/**
 * Common code for all configuration file readers
 */
abstract class Reader
{

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var File
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

	//------------------------------------------------------------------------------------- isEndLine
	/**
	 * @param $line string
	 * @return boolean
	 */
	public function isEndLine(string $line) : bool
	{
		return ($line === '];');
	}

	//----------------------------------------------------------------------------------- isStartLine
	/**
	 * @param $line string
	 * @return boolean
	 */
	public function isStartLine(string $line) : bool
	{
		return ($line === 'return [');
	}

	//------------------------------------------------------------------------------------------ read
	public function read() : void
	{
		$this->readLines();
		$this->readNamespaceUse();
		$this->readBeginLines();
		$this->readConfiguration();
		$this->readEndLines();
	}

	//-------------------------------------------------------------------------------- readBeginLines
	/**
	 * Read begin lines
	 */
	protected function readBeginLines() : void
	{
		$line = current($this->lines);
		if (!$this->file->begin_lines) {
			$this->file->begin_lines = [];
		}
		for ($started = false; ($line !== false) && !$started; $line = next($this->lines)) {
			if ($this->isStartLine($line)) {
				$started = true;
			}
			else {
				$this->file->begin_lines[] = $line;
			}
		}
	}

	//----------------------------------------------------------------------------- readConfiguration
	/**
	 * Read configuration : the main part of the file
	 */
	abstract protected function readConfiguration() : void;

	//---------------------------------------------------------------------------------- readEndLines
	/**
	 * Read end lines
	 */
	protected function readEndLines() : void
	{
		$blank_line = false;
		if (!$this->file->end_lines) {
			$this->file->end_lines = [];
		}
		while (($line = next($this->lines)) !== false) {
			if ($line) {
				if ($blank_line) {
					$blank_line              = false;
					$this->file->end_lines[] = '';
				}
				$this->file->end_lines[] = $line;
			}
			else {
				$blank_line = true;
			}
		}
		if ($blank_line) {
			$this->file->end_lines[] = '';
		}
	}

	//------------------------------------------------------------------------------------- readLines
	/**
	 * Read raw lines from file
	 */
	protected function readLines() : void
	{
		$this->lines = explode(LF, str_replace(CR, '', file_get_contents($this->file->file_name)));
	}

	//------------------------------------------------------------------------------ readNamespaceUse
	/**
	 * Read namespace and use clauses
	 */
	protected function readNamespaceUse() : void
	{
		$php                   = false;
		$this->file->namespace = null;
		$this->file->use       = [];
		for ($line = reset($this->lines); $line !== false; $line = next($this->lines)) {
			if (str_starts_with($line, '<?php')) {
				$php = true;
			}
			elseif ($php) {
				if (str_starts_with($line, 'namespace ')) {
					$this->file->namespace = mParse($line, 'namespace ', ';');
				}
				elseif (str_starts_with($line, 'use ')) {
					$this->file->use[] = mParse($line, 'use ', ';');
				}
				elseif ($line) {
					break;
				}
			}
		}
		usort($this->file->use, function(string $use1, string $use2) : int {
			return strcmp(strtolower($use1), strtolower($use2));
		});
	}

}
