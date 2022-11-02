<?php
namespace ITRocks\Framework\Feature\Lock;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Data_Link\Identifier_Map;
use ITRocks\Framework\Dao\Option\Only;
use ITRocks\Framework\Feature\Lock;
use ITRocks\Framework\Feature\Unlock\Unlockable;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * Apply this to any object that can be locked using a lock button
 *
 * The object can't be modified (or deleted) if it is locked
 *
 * @before_delete isDeletable
 * @before_write  isWritable
 * @duplicate onDuplicateLockable
 */
trait Lockable
{

	//--------------------------------------------------------------------------------------- $locked
	/**
	 * @user hidden
	 * @var boolean
	 */
	public bool $locked = false;

	//----------------------------------------------------------------------------------- isDeletable
	/**
	 * Determines if data were locked for deletion
	 *
	 * @param $link Data_Link
	 * @return boolean
	 */
	public function isDeletable(Data_Link $link) : bool
	{
		return !(
			($link instanceof Identifier_Map)
			&& $link->getObjectIdentifier($this)
			&& $link->searchOne([$this], get_class($this))->locked
		);
	}

	//------------------------------------------------------------------------------------ isWritable
	/**
	 * Determines if data were locked for write
	 * A locked object can be written only if it has an Only('locked') option
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $link    Data_Link
	 * @param $options array|Only[]
	 * @return boolean
	 */
	public function isWritable(Data_Link $link, array &$options) : bool
	{
		if ($this->isDeletable($link)) {
			return true;
		}

		$only = Only::in($options);

		if ($only) {
			$unlocked = true;
			foreach ($only->properties as $property_name) {
				/** @noinspection PhpUnhandledExceptionInspection property must be valid */
				$property = new Reflection_Property($this, $property_name);
				if (!$property->getAnnotation('unlocked')->value) {
					$unlocked = false;
					break;
				}
			}
			if ($unlocked) {
				return true;
			}
		}
		else {
			$property_names = Lock::unlockedProperties($this);
			if ($property_names) {
				$options[] = Dao::only($property_names);
				return true;
			}
		}

		return (
			is_a($this, Unlockable::class)
			&& $only
			&& (count($only->properties) === 1)
			&& (reset($only->properties) === 'locked')
		);
	}

	//--------------------------------------------------------------------------- onDuplicateLockable
	/**
	 * @noinspection PhpUnused @duplicate
	 */
	public function onDuplicateLockable()
	{
		$this->locked = false;
	}

}
