<?php
namespace ITRocks\Framework\Logger\Entry;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Logger\Entry;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Files;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\View;

/**
 * Exports multiple log entries into a unique file, including all logs data (outputs, sql, etc.)
 */
class File_Export
{

	//------------------------------------------------------------------------------ exportLogEntries
	/**
	 * This download uses 'echo' instead of returns because big files may be generated, and should
	 * not entirely be loaded into memory.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $log_entries Entry[]
	 */
	public function exportLogEntries(array $log_entries) : void
	{
		Files::downloadOutput('log-entries.log', 'text/plain');

		foreach ($log_entries as $log_entry) {
			echo str_repeat('=', 100) . LF;
			echo Paths::absoluteBase() . View::link($log_entry) . LF . LF;
			/** @noinspection PhpUnhandledExceptionInspection object */
			foreach ((new Reflection_Class($log_entry))->getProperties() as $property) {
				if ($property->isPublic() && !$property->isStatic()) {
					/** @noinspection PhpUnhandledExceptionInspection isVisible & valid object */
					$value = $property->getValue($log_entry);
					if ($value instanceof Date_Time) {
						$value = Loc::dateToLocale($value);
					}
					elseif (is_object($value)) {
						$value = strval($value);
					}
					echo $property->name . ': ' . print_r($value, true) . LF;
				}
			}
			echo LF;
		}
	}

}
