<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Trigger;

/**
 * Feature execution trigger
 *
 * @override actions @set_store_name feature_trigger_actions
 * @override running @var Feature\Run[]
 * @property Feature\Run[] running
 */
#[Store('feature_triggers')]
class Feature extends Trigger
{
	use Has_Condition;

	//--------------------------------------------------------------------------------- $feature_name
	public string $feature_name;

	//----------------------------------------------------------------------------------------- $when
	#[Values('after, before')]
	public string $when;

}
