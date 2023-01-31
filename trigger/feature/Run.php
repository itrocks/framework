<?php
namespace ITRocks\Framework\Trigger\Feature;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Trigger\Feature;
use ITRocks\Framework\Trigger\Has_Condition;

#[Store('feature_trigger_runs')]
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
