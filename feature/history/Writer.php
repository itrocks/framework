<?php
namespace ITRocks\Framework\Feature\History;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Data_Link\Identifier_Map;
use ITRocks\Framework\Feature\History;
use ITRocks\Framework\Reflection\Attribute\Property\Component;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Stringable;

/**
 * History writer
 *
 * TODO HIGHEST This probably does not record any history if Dao Cache is on !
 */
abstract class Writer
{

	//--------------------------------------------------------------------------------- $before_write
	/**
	 * @var Has_History[][]
	 */
	private static array $before_write = [];

	//------------------------------------------------------------------------------------ afterWrite
	/**
	 * @param $object Has_History
	 * @param $link   Data_Link
	 */
	public static function afterWrite(Has_History $object, Data_Link $link) : void
	{
		$class_name = Builder::className(get_class($object));
		if (
			($link instanceof Identifier_Map)
			&& ($identifier = $link->getObjectIdentifier($object))
			&& isset(self::$before_write[$class_name][$identifier])
		) {
			$before_write = self::$before_write[$class_name][$identifier];
			foreach (self::createHistory($before_write, $object) as $history) {
				Dao::write($history);
			}
			unset(self::$before_write[$class_name][$identifier]);
		}
		// this commit() solves the begin() into beforeWrite()
		Dao::commit();
	}

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object Has_History
	 * @param $link   Data_Link
	 */
	public static function beforeWrite(Has_History $object, Data_Link $link) : void
	{
		// Dao::begin() will be solved into afterWrite()
		Dao::begin();
		if (($link instanceof Identifier_Map) && ($identifier = $link->getObjectIdentifier($object))) {
			$class_name = Builder::className(get_class($object));
			if (!isset(self::$before_write[$class_name])) {
				self::$before_write[$class_name] = [];
			}
			self::$before_write[$class_name][$identifier] = $before = $link->read(
				$identifier, $class_name
			);
			// call getter for collections and maps in order to get the full value before write
			/** @noinspection PhpUnhandledExceptionInspection from object */
			foreach ((new Reflection_Class($class_name))->getProperties() as $property) {
				if ($property->getType()->isMultiple()) {
					/** @noinspection PhpUnhandledExceptionInspection $property from class and accessible */
					$property->getValue($before);
				}
			}
		}
	}

	//--------------------------------------------------------------------------------- createHistory
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $before Has_History
	 * @param $after  Has_History
	 * @return History[]
	 */
	private static function createHistory(Has_History $before, Has_History $after) : array
	{
		/** @noinspection PhpUnhandledExceptionInspection valid history class name */
		$history_class = new Reflection_Class(Builder::className($after->getHistoryClassName()));
		$history       = [];
		/** @noinspection PhpUnhandledExceptionInspection object */
		$class = new Reflection_Class($before);
		foreach ($class->getProperties() as $property) {
			$type = $property->getType();
			if (
				!($type->isSingleClass() && Component::of($property)?->value)
				&& !Store::of($property)->isFalse()
				&& !$property->getType()->isInstanceOf(History::class)
			) {
				/** @noinspection PhpUnhandledExceptionInspection $property from class and accessible */
				$new_value = $property->getValue($after);
				/** @noinspection PhpUnhandledExceptionInspection $property from class and accessible */
				$old_value = $property->getValue($before);
				if (is_array($new_value)) {
					$new_value = join(', ', $new_value);
				}
				if (is_array($old_value)) {
					$old_value = join(', ', $old_value);
				}
				// TODO To be removed on PHP 7.4 : useless with hard typing
				if ($type->isBoolean() && !$type->isMultiple()) {
					$new_value = boolval($new_value);
					$old_value = boolval($old_value);
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
								&& strval($old_value) !== strval($new_value)
							)
						)
					)
					|| (strval($old_value) !== strval($new_value))
				) {
					/** @noinspection PhpUnhandledExceptionInspection valid history class name */
					$history[] = Builder::create(
						$history_class->name, [$after, $property->name, $old_value, $new_value]
					);
				}
			}
		}
		return $history;
	}

}
