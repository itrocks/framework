<?php
namespace SAF\Framework\History;

use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Dao\Data_Link;
use SAF\Framework\Dao\Data_Link\Identifier_Map;
use SAF\Framework\Dao\Option;
use SAF\Framework\History;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Tools\Stringable;

/**
 * History writer
 */
abstract class Writer
{

	//--------------------------------------------------------------------------------- $before_write
	/**
	 * @var Has_History
	 */
	private static $before_write;

	//------------------------------------------------------------------------------------ afterWrite
	/**
	 * @param $object Has_History
	 * @param $link Data_Link
	 */
	public static function afterWrite(Has_History $object, Data_Link $link)
	{
		if (
			($link instanceof Identifier_Map)
			&& ($identifier = $link->getObjectIdentifier($object))
			&& isset(self::$before_write[get_class($object)][$identifier])
		) {
			/** @var $before_write Has_History */
			$before_write = self::$before_write[get_class($object)][$identifier];
			/** @var $after_write Has_History */
			$after_write = $link->read($identifier, get_class($object));
			foreach (self::createHistory($before_write, $after_write) as $history) {
				Dao::write($history);
			}
			unset(self::$before_write[get_class($object)][$identifier]);
		}
	}

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * @param $object Has_History
	 * @param $link Data_Link
	 */
	public static function beforeWrite(Has_History $object, Data_Link $link)
	{
		if (($link instanceof Identifier_Map) && ($identifier = $link->getObjectIdentifier($object))) {
			/** @noinspection PhpUndefinedFieldInspection */
			self::$before_write[get_class($object)][$identifier] = $before = $link->read(
				$identifier, get_class($object)
			);
			// call getter for collections and maps in order to get the full value before write
			foreach ((new Reflection_Class(get_class($before)))->accessProperties() as $property) {
				if ($property->gettype()->isMultiple()) {
					$property->getValue($before);
				}
			}
		}
	}

	//--------------------------------------------------------------------------------- createHistory
	/**
	 * @param $before Has_History
	 * @param $after  Has_History
	 * @return History[]
	 */
	private static function createHistory(Has_History $before, Has_History $after)
	{
		$history_class = new Reflection_Class(Builder::className($after->getHistoryClassName()));
		$history = [];
		$class = new Reflection_Class(get_class($before));
		foreach ($class->accessProperties() as $property) {
			$old_value = $property->getValue($before);
			$new_value = $property->getValue($after);
			if (is_array($old_value)) {
				$old_value = join(', ', $old_value);
			}
			if (is_array($new_value)) {
				$new_value = join(', ', $new_value);
			}
			if (
				(
					(is_object($old_value) || is_object($new_value))
					&& (
						(
							(Dao::getObjectIdentifier($old_value) || Dao::getObjectIdentifier($new_value))
							&& !Dao::is($old_value, $new_value)
						)
						|| (
							($old_value instanceof Stringable) && ($new_value instanceof Stringable)
							&& strval($old_value) != strval($new_value)
						)
					)
				)
				|| (strval($old_value) != strval($new_value))
			) {
				$history[] = Builder::create(
					$history_class->name, [$after, $property->name, $old_value, $new_value]
				);
			}
		}
		return $history;
	}

}
