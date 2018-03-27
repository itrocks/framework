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

	//----------------------------------------------------------------------------------------- write
	public function write()
	{
		$this->lines = ['<?php'];
		$this->writeNamespace();
		$this->writeUse();
		$this->writeBeginLines();
		$this->writeConfiguration();
		$this->writeEndLines();
		$this->writeLines();
	}

	//------------------------------------------------------------------------------- writeBeginLines
	protected function writeBeginLines()
	{
		if ($this->file->begin_lines) {
			$this->lines = array_merge($this->lines, $this->file->begin_lines);
			if (static::BEGIN_ENDS_WHITE) {
				$this->lines[] = '';
			}
		}
	}

	//---------------------------------------------------------------------------- writeConfiguration
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
	protected function writeEndLines()
	{
		if ($this->file->end_lines) {
			$this->lines = array_merge($this->lines, $this->file->end_lines);
		}
	}

	//------------------------------------------------------------------------------------ writeLines
	protected function writeLines()
	{
		$read_buffer  = file_get_contents($this->file->file_name);
		$write_buffer = join(LF, $this->lines);
		if ($read_buffer !== $write_buffer) {
			file_put_contents($this->file->file_name, $write_buffer);
		}
	}

	//-------------------------------------------------------------------------------- writeNamespace
	protected function writeNamespace()
	{
		if ($this->file->namespace) {
			$this->lines[] = 'namespace ' . $this->file->namespace . ';';
			$this->lines[] = '';
		}
	}

	//-------------------------------------------------------------------------------------- writeUse
	protected function writeUse()
	{
		if ($this->file->use) {
			foreach ($this->file->use as $use) {
				$this->lines[] = 'use ' . $use . ';';
			}
			$this->lines[] = '';
		}
	}

}
