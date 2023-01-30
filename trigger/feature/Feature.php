<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Reflection\Attribute\Class_\Store_Name;
use ITRocks\Framework\Trigger;

/**
 * Feature execution trigger
 *
 * @override actions @set_store_name feature_trigger_actions
 * @override running @var Feature\Run[]
 * @property Feature\Run[] running
 */
#[Store_Name('feature_triggers')]
class Feature extends Trigger
{
	use Has_Condition;

	//--------------------------------------------------------------------------------- $feature_name
	/**
	 * @var string
	 */
	public string $feature_name;

	//----------------------------------------------------------------------------------------- $when
	/**
	 * @values after, before
	 * @var string
	 */
	public string $when;

}
