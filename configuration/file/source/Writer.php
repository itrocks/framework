<?php
namespace ITRocks\Framework\Configuration\File\Source;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Source;

/**
 * Builder class source file writer
 *
 * @override file @var Source
 * @property Source file
 */
class Writer extends File\Writer
{

	//------------------------------------------------------------------------------ BEGIN_ENDS_WHITE
	const BEGIN_ENDS_WHITE = false;

	//------------------------------------------------------------- Line length calculation constants
	const MAX_LINE_LENGTH = 100;
	const TAB_LENGTH      = 2;

	//------------------------------------------------------------------------------------ lineLength
	/**
	 * Calculates the length of a line, in characters
	 *
	 * This changes tabs into two-spaces for counting
	 *
	 * @param $line string
	 * @return integer
	 */
	protected function lineLength($line)
	{
		$tab_count = substr_count($line, TAB);
		return strlen($line) + ($tab_count * (static::TAB_LENGTH - 1));
	}

	//------------------------------------------------------------------------------ shortClassNameOf
	/**
	 * Simplify the name of the class using its longest reference into use,
	 * or its start from the current namespace
	 *
	 * @param $class_name        string
	 * @param $maximum_use_depth integer do not care about use greater than this backslashes counter
	 * @return string
	 */
	protected function shortClassNameOf($class_name, $maximum_use_depth = 999)
	{
		$final_class_name = parent::shortClassNameOf($class_name, $maximum_use_depth);
		if (strpos($final_class_name, BS) && (lLastParse($class_name, BS) === $this->file->namespace)) {
			$final_class_name = rLastParse($class_name, BS);
		}
		return $final_class_name;
	}

	//--------------------------------------------------------------------------- writeClassPrototype
	/**
	 * Write class prototype : all lines from 'class Class_Name' to '{'
	 *
	 * - Use $file's $class_type, $class_name, $class_extends and $class_implements property values
	 * - Limit to the MAX_LINE_LENGTH characters limit
	 */
	protected function writeClassPrototype()
	{
		$class_prototype = $this->file->class_type . SP
			. $this->shortClassNameOf($this->file->class_name);
		if ($this->file->class_extends) {
			$class_extends = 'extends ' . $this->shortClassNameOf($this->file->class_extends);
			if ($this->lineLength($class_prototype . SP . $class_extends) > static::MAX_LINE_LENGTH) {
				$this->lines[]   = $class_prototype;
				$class_prototype = TAB . $class_extends;
			}
			else {
				$class_prototype .= SP . $class_extends;
			}
		}
		if ($this->file->class_implements) {
			$class_implements = [];
			foreach ($this->file->class_implements as $implements) {
				$class_implements[] = $this->shortClassNameOf($implements);
			}
			$class_implements = 'implements ' . join(', ', $class_implements);
			if ($this->lineLength($class_prototype . SP . $class_implements) > static::MAX_LINE_LENGTH) {
				$this->lines[]   = $class_prototype;
				$class_prototype = TAB . $class_implements;
			}
			else {
				$class_prototype .= SP . $class_implements;
			}
			while (
				strpos($class_prototype, ', ')
				&& ($this->lineLength($class_prototype) > static::MAX_LINE_LENGTH)
			) {
				$next_lines = '';
				while (
					strpos($class_prototype, ', ')
					&& ($this->lineLength($class_prototype) > static::MAX_LINE_LENGTH)
				) {
					$next_lines      = rLastParse($class_prototype, ', ');
					$class_prototype = lLastParse($class_prototype, ', ') . ',';
				}
				$this->lines[]   = $next_lines;
				$class_prototype = TAB . TAB . $next_lines;
			}
		}
		$this->lines[] = $class_prototype;
		$this->lines[] = '{';
	}

	//--------------------------------------------------------------------------------- writeClassUse
	protected function writeClassUse()
	{
		foreach ($this->file->class_use as $class_use) {
			if (is_object($class_use)) {
				$this->lines[] = TAB . 'use' . SP . $this->shortClassNameOf($class_use->trait_name)
					. (beginsWith($class_use->rules, '{') ? SP : '')
					. $class_use->rules;
			}
			else {
				$this->lines[] = $class_use;
			}
		}
	}

	//---------------------------------------------------------------------------- writeConfiguration
	/**
	 * Write builder configuration to lines
	 */
	protected function writeConfiguration()
	{
		$this->writeClassPrototype();
		$this->writeClassUse();
	}

}
