<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Trigger;

/**
 * Feature execution trigger
 *
 * @override actions @set_store_name trigger_actions_features
 * @override running @var Feature\Run[]
 * @property Feature\Run[] running
 * @store_name feature_triggers
 */
class Feature extends Trigger
{
	use Has_Condition;

}
