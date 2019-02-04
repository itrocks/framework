<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Trigger;

/**
 * Feature execution trigger
 *
 * @override actions @set_store_name feature_trigger_actions
 * @override running @var Feature\Run[]
 * @property Feature\Run[] running
 * @store_name feature_triggers
 */
class Feature extends Trigger
{
	use Has_Condition;

	//--------------------------------------------------------------------------------- $feature_name
	/**
	 * @var string
	 */
	public $feature_name;

	//----------------------------------------------------------------------------------------- $when
	/**
	 * @values after, before
	 * @var string
	 */
	public $when;

}
