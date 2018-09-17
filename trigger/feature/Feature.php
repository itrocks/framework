<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Trigger;

/**
 * Feature execution trigger
 *
 * @override running @var Feature\Run[]
 * @property Feature\Run[] running
 * @store_name feature_triggers
 */
class Feature extends Trigger
{
	use Has_Condition;

}
