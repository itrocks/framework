<?php
namespace ITRocks\Framework\Trigger\Change;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Composite;
use ITRocks\Framework\Trigger\Action;
use ITRocks\Framework\Trigger\Change;
use ITRocks\Framework\Trigger\Has_Condition;

#[Store('change_trigger_runs')]
class Run extends Has_Condition\Run
{

	//-------------------------------------------------------------------------------------- $actions
	/**
	 * @var Action[]
	 */
	public array $actions;

	//--------------------------------------------------------------------------------------- $change
	#[Composite]
	public Change $change;

}
