<?php
namespace ITRocks\Framework\Configuration\File\Local_Access;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Local_Access;

/**
 * Local access configuration file writer
 *
 * @override file @var Local_Access
 * @property Local_Access file
 */
class Reader extends File\Reader
{

	//----------------------------------------------------------------------------- readConfiguration
	protected function readConfiguration() : void
	{
		$this->file->lines = [];
		$line              = current($this->lines);
		for ($ended = false; ($line !== false) && !$ended; $line = next($this->lines)) {
			if (str_starts_with($line, TAB . Q)) {
				$this->file->lines[] = mLastParse($line, Q, Q);
			}
			elseif (str_starts_with($line, TAB . DQ)) {
				$this->file->lines[] = mLastParse($line, DQ, DQ);
			}
			elseif ($this->isEndLine($line)) {
				$ended = true;
			}
		}
	}

}
