<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Configuration\File;

/**
 * Common code for all configuration file writers
 */
class Writer
{

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

	//------------------------------------------------------------------------------ shortClassNameOf
	/**
	 * Simplify the name of the class using its longest reference into use,
	 * or its start from the current namespace
	 *
	 * @param $class_name        string
	 * @param $maximum_use_depth integer do not care about use greater than t
	 * @return string
	 */
	protected function shortClassNameOf($class_name, $maximum_use_depth = 999)
	{
		$final_class_name = null;
		$used             = '';
		foreach ($this->file->use as $use) {
			if (
				beginsWith($class_name, $use)
				&& (strlen($use) > strlen($used))
				&& (substr_count($use, BS) < $maximum_use_depth)
			) {
				$final_class_name = rParse($class_name, BS, substr_count($use, BS));
				$used             = $use;
			}
		}
		if (
			beginsWith($class_name, $this->file->namespace)
			&& (strlen($this->file->namespace) > strlen($used))
		) {
			$final_class_name = substr($class_name, strlen($this->file->namespace));
		}
		return $final_class_name ?: (BS . $class_name);
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
			$this->lines   = array_merge($this->lines, $this->file->begin_lines);
			$this->lines[] = '';
		}
	}

	//---------------------------------------------------------------------------- writeConfiguration
	protected function writeConfiguration()
	{
		if ($this instanceof Has_Configuration_Accessors) {
			$configuration_lines = $this->getConfigurationLines();
			if ($configuration_lines) {
				$this->lines   = array_merge($this->lines,  $configuration_lines);
				$this->lines[] = '';
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
