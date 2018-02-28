<?php
namespace ITRocks\Framework\Configuration;

use ITRocks\Framework\Configuration\File\Has_Add_To_Configuration;
use ITRocks\Framework\Configuration\File\Has_File_Name;

/**
 * Configuration file
 */
abstract class File
{
	use Has_File_Name;

	//---------------------------------------------------------------------------------- $begin_lines
	/**
	 * free code lines before the parsed configuration started
	 *
	 * @var string[]
	 */
	public $begin_lines;

	//------------------------------------------------------------------------------------ $end_lines
	/**
	 * free code lines after the parsed configuration ended
	 *
	 * @var string[]
	 */
	public $end_lines;

	//------------------------------------------------------------------------------------ $namespace
	/**
	 * @var string
	 */
	public $namespace;

	//------------------------------------------------------------------------------------------ $use
	/**
	 * @var string[]
	 */
	public $use;

	//------------------------------------------------------------------------------------- isEndLine
	/**
	 * @param $line string
	 * @return boolean
	 */
	public function isEndLine($line)
	{
		return ($line === '];');
	}

	//----------------------------------------------------------------------------------- isStartLine
	/**
	 * @param $line string
	 * @return boolean
	 */
	public function isStartLine($line)
	{
		return ($line === 'return [');
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * @return static
	 */
	public function read()
	{
		$lines = explode(LF, str_replace(CR, '', file_get_contents($this->file_name)));
		$this
			->readNamespaceUse($lines)
			->readBeginLines($lines)
			->readConfiguration($lines)
			->readEndLines($lines);
		return $this;
	}

	//-------------------------------------------------------------------------------- readBeginLines
	/**
	 * @param $lines string[]
	 * @return static
	 */
	protected function readBeginLines(array &$lines)
	{
		$this->begin_lines = [];
		for (($line = current($lines)), ($started = false); !$started; $line = next($lines)) {
			if ($this->isStartLine($line)) {
				$started = true;
			}
			else {
				$this->begin_lines[] = $line;
			}
		}
		return $this;
	}

	//----------------------------------------------------------------------------- readConfiguration
	/**
	 * @param $lines string[]
	 * @return static
	 */
	protected function readConfiguration(array &$lines)
	{
		for (($line = current($lines)), ($ended = false); !$ended; $line = next($lines)) {
			if ($this->isEndLine($line)) {
				$ended = true;
			}
			elseif ($this instanceof Has_Add_To_Configuration) {
				$this->addToConfiguration($line);
			}
		}
		return $this;
	}

	//---------------------------------------------------------------------------------- readEndLines
	/**
	 * @param $lines string[]
	 * @return static
	 */
	protected function readEndLines(array &$lines)
	{
		$this->end_lines = [];
		while ($line = next($lines)) {
			$this->end_lines[] = $line;
		}
		return $this;
	}

	//------------------------------------------------------------------------------ readNamespaceUse
	/**
	 * @param $lines string[]
	 * @return static
	 */
	protected function readNamespaceUse(array &$lines)
	{
		$php             = false;
		$this->namespace = null;
		$this->use       = [];
		for ($line = reset($lines); true; $line = next($lines)) {
			if (beginsWith($line, '<?php')) {
				$php = true;
			}
			elseif ($php) {
				if (beginsWith($line, 'namespace ')) {
					$this->namespace = mParse($line, 'namespace ', ';');
				}
				elseif (beginsWith($line, 'use ')) {
					$this->use[] = mParse($line, 'use ', ';');
				}
				elseif ($line) {
					break;
				}
			}
		}
		sort($this->use);
		return $this;
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * @return static
	 */
	public function write()
	{
		return $this;
	}

}
