<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Reflection\Type;

/**
 * Common code for all file readers
 */
class Reader
{

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var File
	 */
	public $file;

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

	//------------------------------------------------------------------------------- fullClassNameOf
	/**
	 * Change a short class name, with maybe spaces and trailing '::class', into a clean full class
	 * name with full namespace
	 *
	 * It uses $namespace and $use for namespace completion
	 *
	 * @param $class_name string source short class name to cleanup and extend
	 * @return string resulting full and clean class name
	 */
	protected function fullClassNameOf($class_name)
	{
		$class_name = lParse(trim($class_name), '::class');
		return (new Type($class_name))->applyNamespace($this->file->namespace, $this->file->use);
	}

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
	public function read()
	{
		$this->readLines()
			->readNamespaceUse()
			->readBeginLines()
			->readConfiguration()
			->readEndLines();
	}

	//-------------------------------------------------------------------------------- readBeginLines
	/**
	 * @return static
	 */
	protected function readBeginLines()
	{
		$this->file->begin_lines = [];
		$line = current($this->lines);
		for ($started = false; !$started; $line = next($this->lines)) {
			if ($this->isStartLine($line)) {
				$started = true;
			}
			else {
				$this->file->begin_lines[] = $line;
			}
		}
		return $this;
	}

	//----------------------------------------------------------------------------- readConfiguration
	/**
	 * @return static
	 */
	protected function readConfiguration()
	{
		$line = current($this->lines);
		for ($ended = false; !$ended; $line = next($this->lines)) {
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
	 * @return static
	 */
	protected function readEndLines()
	{
		$this->file->end_lines = [];
		while ($line = next($this->lines)) {
			$this->file->end_lines[] = $line;
		}
		return $this;
	}

	//------------------------------------------------------------------------------------- readLines
	/**
	 * @return static
	 */
	protected function readLines()
	{
		$this->lines = explode(LF, str_replace(CR, '', file_get_contents($this->file->file_name)));
		return $this;
	}

	//------------------------------------------------------------------------------ readNamespaceUse
	/**
	 * @return static
	 */
	protected function readNamespaceUse()
	{
		$php                   = false;
		$this->file->namespace = null;
		$this->file->use       = [];
		for ($line = reset($this->lines); true; $line = next($this->lines)) {
			if (beginsWith($line, '<?php')) {
				$php = true;
			}
			elseif ($php) {
				if (beginsWith($line, 'namespace ')) {
					$this->file->namespace = mParse($line, 'namespace ', ';');
				}
				elseif (beginsWith($line, 'use ')) {
					$this->file->use[] = mParse($line, 'use ', ';');
				}
				elseif ($line) {
					break;
				}
			}
		}
		sort($this->file->use);
		return $this;
	}

}