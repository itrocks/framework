<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Traits\Has_Name;
use ITRocks\Framework\Trigger\Action;
use ITRocks\Framework\Widget\Map_As_Collection;

/**
 * A trigger calculates if an action must be run
 */
#[Store]
abstract class Trigger
{
	use Has_Name;

	//-------------------------------------------------------------------------------------- $actions
	/**
	 * @link Map
	 * @see Map_As_Collection
	 * @var Action[]
	 * @widget Map_As_Collection
	 */
	public array $actions;

	//-------------------------------------------------------------------------------- executeActions
	/**
	 * Tells the trigger server it can run the actions
	 *
	 * @param $object object|string can receive a context object or class name
	 * @return Action[]
	 */
	public function executeActions(object|string $object) : array
	{
		$actions = [];
		foreach ($this->actions as $action) {
			$scheduled_action = $action->execute($object);
			if ($scheduled_action) {
				$actions[] = $scheduled_action;
			}
		}
		return $actions;
	}

}
