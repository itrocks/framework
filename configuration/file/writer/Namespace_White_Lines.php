<?php
namespace ITRocks\Framework\Configuration\File\Writer;

use ITRocks\Framework\Configuration\File\Writer;
use ITRocks\Framework\Reflection\Attribute\Class_\Extends_;

#[Extends_(Writer::class)]
trait Namespace_White_Lines
{

	//--------------------------------------------------------------------------------- $insert_lines
	/**
	 * @var string[]
	 */
	protected array $insert_lines;

	//------------------------------------------------------------------------------- $last_namespace
	/**
	 * @var string
	 */
	protected string $last_namespace;

	//--------------------------------------------------------------------------------- autoWhiteLine
	/**
	 * @param $short_class_name string
	 */
	protected function autoWhiteLine(string $short_class_name) : void
	{
		$namespace = lParse($short_class_name, BS);
		if ($this->last_namespace !== $namespace) {
			if ($this->last_namespace) {
				$this->lines[] = '';
			}
			$this->writeInsertLines();
			$this->insert_lines   = [];
			$this->last_namespace = $namespace;
		}
	}

	//--------------------------------------------------------------------------------- initWhiteLine
	protected function initWhiteLine() : void
	{
		$this->insert_lines   = [];
		$this->last_namespace = '';
	}

	//------------------------------------------------------------------------------ writeInsertLines
	protected function writeInsertLines() : void
	{
		foreach ($this->insert_lines as $line) {
			$this->lines[] = $line;
		}
	}

}
