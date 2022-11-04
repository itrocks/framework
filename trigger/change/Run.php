<?php
namespace ITRocks\Framework\Trigger\Change;

use ITRocks\Framework\Trigger\Action;
use ITRocks\Framework\Trigger\Change;
use ITRocks\Framework\Trigger\Has_Condition;

/**
 * @store_name change_trigger_runs
 */
class Run extends Has_Condition\Run
{

	//-------------------------------------------------------------------------------------- $actions
	/**
	 * @link Map
	 * @var Action[]
	 */
	public array $actions;

	//--------------------------------------------------------------------------------------- $change
	/**
	 * @composite
	 * @link Object
	 * @var Change
	 */
	public Change $change;

}
