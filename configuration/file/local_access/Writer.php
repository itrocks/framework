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
class Writer extends File\Writer
{

	//---------------------------------------------------------------------------- writeConfiguration
	protected function writeConfiguration()
	{
		$this->lines[] = 'return [';
		$counter       = count($this->file->lines);
		foreach ($this->file->lines as $value) {
			$quote = str_contains($value, Q) ? DQ : Q;
			if (($quote === DQ) && str_contains($value, DQ)) {
				$value = str_replace(DQ, BQ . DQ, $value);
			}
			$this->lines[] = TAB . $quote . $value . $quote . ((--$counter) ? ',' : '');
		}
		$this->lines[] = '];';
	}

}
