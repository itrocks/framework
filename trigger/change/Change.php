<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Trigger;
use ITRocks\Framework\Trigger\Change\Plugin;

/**
 * Data change trigger
 *
 * @after_write resetPluginCache
 * @before_delete resetPluginCache
 * @override actions @set_store_name change_trigger_actions
 * @override running @var Change\Run[]
 * @property Change\Run[] running
 * @store_name change_triggers
 */
class Change extends Trigger
{
	use Has_Condition;

	//------------------------------------------------------------------------------ resetPluginCache
	public function resetPluginCache()
	{
		if (!$this->class_name || !($plugin = Plugin::get())) {
			return;
		}
		$plugin->resetCache($this->class_name);
	}

}
