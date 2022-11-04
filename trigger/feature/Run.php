<?php
namespace ITRocks\Framework\Trigger\Feature;

use ITRocks\Framework\Trigger\Feature;
use ITRocks\Framework\Trigger\Has_Condition;

/**
 * @store_name feature_trigger_runs
 */
class Run extends Has_Condition\Run
{

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * @composite
	 * @link Object
	 * @var Feature
	 */
	public Feature $feature;

}
