<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Trigger;

/**
 * Data change trigger
 *
 * @override actions @set_store_name trigger_actions_changes
 * @override running @var Change\Run[]
 * @property Change\Run[] running
 * @store_name change_triggers
 */
class Change extends Trigger
{
	use Has_Condition;

}
