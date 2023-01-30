<?php
namespace ITRocks\Framework\Trigger\Feature;

use ITRocks\Framework\Reflection\Attribute\Class_\Store_Name;
use ITRocks\Framework\Trigger\Feature;
use ITRocks\Framework\Trigger\Has_Condition;

#[Store_Name('feature_trigger_runs')]
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
