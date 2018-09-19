<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Trigger\Action;
use ITRocks\Framework\Widget\Edit\Widgets\Map_As_Collection;

/**
 * A trigger calculates if an action must be run
 *
 * @business
 */
abstract class Trigger
{

	//-------------------------------------------------------------------------------------- $actions
	/**
	 * @link Map
	 * @see Map_As_Collection
	 * @var Action[]
	 * @widget Map_As_Collection
	 */
	public $actions;

	//-------------------------------------------------------------------------------- executeActions
	/**
	 * Tells the trigger server it can run the actions
	 *
	 * @param $object object|string can receive a context object or class name
	 */
	public function executeActions($object)
	{
		foreach ($this->actions as $action) {
			if (!$action->running) {
				$action->execute($object);
			}
		}
	}

}
