<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Trigger;
use ITRocks\Framework\Trigger\Change\Plugin;

/**
 * Data change trigger
 *
 * @after_write resetPluginCache
 * @before_delete resetPluginCache
 * @display_order name, class_name, before_condition, after_condition, actions
 * @override actions @set_store_name change_trigger_actions @var Change\Action[]
 * @override running @var Change\Run[]
 * @property Change\Action[] actions
 * @property Change\Run[]    running
 * @store_name change_triggers
 */
class Change extends Trigger
{
	use Has_Condition;

	//------------------------------------------------------------------------------ resetPluginCache
	public function resetPluginCache()
	{
		if (!$this->class_name || !Plugin::registered()) {
			return;
		}
		Plugin::get()->resetCache($this->class_name);
	}

}
