<?php
namespace ITRocks\Framework\Trigger\Has_Condition;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\Setter;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Traits\Date_Logged;

#[Class_\Store]
abstract class Run
{
	use Component;
	use Date_Logged;

	//----------------------------------------------------------------------------------------- AFTER
	/**
	 * Step 2 (only if $after_conditions)
	 * In condition checking stage : 'after' conditions have to be verified (not done yet)
	 */
	const AFTER = 'after';

	//---------------------------------------------------------------------------------------- BEFORE
	/**
	 * Step 1 (only if $before_conditions)
	 * In condition checking stage : 'before' conditions have been verified, but we are waiting for
	 * 'after' conditions to be launched
	 */
	const BEFORE = 'before';

	//-------------------------------------------------------------------------------------- COMPLETE
	/**
	 * Step 5
	 * All run actions are complete
	 */
	const COMPLETE = 'complete';

	//--------------------------------------------------------------------------------------- PARTIAL
	/**
	 * Step 5
	 * Some run actions are complete, some are still running
	 */
	const PARTIAL = 'partial';

	//--------------------------------------------------------------------------------------- PENDING
	/**
	 * Step 3
	 * All conditions have been verified : we can run the actions
	 */
	const PENDING = 'pending';

	//--------------------------------------------------------------------------------------- RUNNING
	/**
	 * Step 4
	 * The actions are running, waiting for them to be complete
	 */
	const RUNNING = 'running';

	//----------------------------------------------------------------------------------- $class_name
	public string $class_name = '';

	//----------------------------------------------------------------------------------- $identifier
	public ?int $identifier = null;

	//--------------------------------------------------------------------------------------- $object
	#[Getter('getObject'), Setter('setObject'), Store(false)]
	public ?object $object;

	//----------------------------------------------------------------------------------------- $step
	/**
	 * @ordered_values
	 * @values before, after, pending, running, complete
	 */
	public string $step;

	//------------------------------------------------------------------------------------- getObject
	protected function getObject() : ?object
	{
		return ($this->class_name && $this->identifier)
			? Dao::read($this->identifier, $this->class_name)
			: null;
	}

	//------------------------------------------------------------------------------------- setObject
	/**
	 * @noinspection PhpUnused #Setter
	 */
	protected function setObject(?object $object) : void
	{
		if (!$object || !($identifier = Dao::getObjectIdentifier($object))) {
			trigger_error('You must set a stored object', E_USER_ERROR);
			/** @noinspection PhpUnreachableStatementInspection Error may be captured for continue */
			$this->class_name = '';
			$this->identifier = null;
			return;
		}
		$this->class_name = Builder::current()->sourceClassName(get_class($object));
		$this->identifier = $identifier;
	}

}
