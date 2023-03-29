<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Trigger;
use ITRocks\Framework\Trigger\Change\Plugin;

/**
 * Data change trigger
 *
 * @after_write resetPluginCache
 * @before_delete resetPluginCache
 * @override actions @set_store_name change_trigger_actions @var Change\Action[]
 * @override running @var Change\Run[]
 * @property Change\Action[] actions
 * @property Change\Run[]    running
 */
#[
	Display_Order('name', 'class_name', 'before_condition', 'after_condition', 'actions'),
	Store('change_triggers')
]
class Change extends Trigger
{
	use Has_Condition;

	//------------------------------------------------------------------------------ resetPluginCache
	/**
	 * @noinspection PhpUnused @after_write, @before_delete
	 */
	public function resetPluginCache() : void
	{
		if (!$this->class_name || !Plugin::registered()) {
			return;
		}
		Plugin::get()->resetCache($this->class_name);
	}

}
