<?php
namespace ITRocks\Framework\Configuration\File\Source;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Source;

/**
 * Class source file reader
 *
 * @override file @var Source
 * @property Source file
 */
class Reader extends File\Reader
{

	//--------------------------------------------------------------------------------- $class_buffer
	/**
	 * The buffer that contains the declaration of the class : 'class ... extends ... implements ...'
	 *
	 * @var string
	 */
	protected $class_buffer;

	//------------------------------------------------------------------------------------- isEndLine
	/**
	 * @param $line string
	 * @return boolean
	 */
	public function isEndLine($line)
	{
		$keyword = lParse(rLastParse($line, TAB, 1, true), SP);
		return ($line === '}')
			|| in_array(
				$keyword, ['const', 'function', 'private', 'protected', 'public', 'static', 'var']
			);
	}

	//----------------------------------------------------------------------------------- isStartLine
	/**
	 * @param $line string
	 * @return boolean
	 */
	public function isStartLine($line)
	{
		$start_line = in_array(lParse($line, SP), ['abstract', 'class', 'interface', 'trait']);
		if ($start_line) {
			$this->class_buffer = $line;
		}
		return $start_line;
	}

	//----------------------------------------------------------------------------- readConfiguration
	/**
	 * Read configuration : the main part of the file
	 */
	protected function readConfiguration()
	{
		$class_text = '';
		/** @var $class_use Class_Use */
		$class_use     = null;
		$end_lines     = [];
		$line          = prev($this->lines);
		$next_line_use = false;
		$opened_class  = false;
		while (($line !== false) && !$this->isEndLine($line)) {
			if ($opened_class) {
				if ($next_line_use || beginsWith(lParse(trim($line), SP), 'use')) {
					if ($end_lines) {
						foreach ($end_lines as $end_line) {
							$this->file->class_use[] = $end_line;
						}
						$end_lines = [];
					}
					$next_line_use = false;
					if (strpos($line, '{')) {
						$parse_char = '{';
					}
					elseif (strpos($line, ';')) {
						$parse_char = ';';
					}
					elseif (strpos($line, ',')) {
						$next_line_use = true;
						$parse_char    = ',';
					}
					else {
						trigger_error('Cannot parse class use clause ' . $line, E_USER_ERROR);
						$parse_char = null;
					}
					if ($parse_char) {
						$use = $this->file->fullClassNameOf(trim(
							(strpos($line, 'use') !== false)
							? mParse($line, 'use ', $parse_char)
							: lParse($line, $parse_char)
						));
						$class_use = new Class_Use($use, $parse_char . rParse($line, $parse_char));
						if (($parse_char !== '{') || strpos($line, '}')) {
							$this->file->class_use[] = $class_use;
							$class_use               = null;
						}
					}
				}
				elseif ($class_use) {
					$class_use->rules .= LF . $line;
					if (strpos($line, '}')) {
						$this->file->class_use[] = $class_use;
						$class_use               = null;
					}
				}
				else {
					$end_lines[] = $line;
				}
			}
			elseif ($line === '{') {
				$class_text  .= lParse($line, '{');
				$opened_class = true;
			}
			else {
				$class_text .= $line;
			}
			$line = next($this->lines);
		}
		$end_lines[]           = $line;
		$into                  = null;
		$this->file->end_lines = $end_lines;
		// extract class, extends and implements names
		foreach (explode(SP, $class_text) as $elements) {
			foreach (explode(',', $elements) as $element) {
				$element = trim($element);
				if (($element === 'abstract') && !$this->file->class_type) {
					$this->file->class_abstract = true;
				}
				if (in_array($element, ['class', 'interface', 'trait'])) {
					$this->file->class_type = $into = $element;
				}
				elseif (in_array($elements, ['extends', 'implements'])) {
					$into = $element;
				}
				elseif ($element && $into) {
					switch ($into) {
						case 'class':
						case 'interface':
						case 'trait':
							$this->file->class_name = $this->file->fullClassNameOf($element);
							break;
						case 'extends':
							$this->file->class_extends = $this->file->fullClassNameOf($element);
							break;
						case 'implements':
							$this->file->class_implements[] = $this->file->fullClassNameOf($element);
							break;
					}
				}
			}
		}
	}

}
