<?php
namespace ITRocks\Framework\Updater\Migrate;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql;

/**
 * Serialized settings migration
 */
trait Settings
{
	use Serialized;

	//------------------------------------------------------------------------------- migrateSettings
	/**
	 * Migrate data stored into settings / user settings
	 *
	 * @param $search_replaces array string[][] [string $search => string $replace]
	 */
	public function migrateSettings(array $search_replaces) : void
	{
		$link = Dao::current();
		if (!($link instanceof Mysql\Link)) {
			return;
		}
		foreach (['settings', 'user_settings'] as $table) {
			$result = $link->query('SELECT id, `value`' . " FROM `$table`");
			while ($record = $result->fetch_row()) {
				[$id, $value] = $record;
				if ($this->replaceMultiple($value, $search_replaces)) {
					$query = "UPDATE `$table`" . ' SET `value` = ' . DQ . $link->escapeString($value) . DQ
						. ' WHERE id = ' . $id;
					$link->query($query);
				}
			}
			$result->close();
		}
	}

}
