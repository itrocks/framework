<?php
namespace ITRocks\Framework\Trigger\Change;

use ITRocks\Framework\Trigger\Change;
use ITRocks\Framework\Trigger\Has_Condition;

/**
 * @store_name change_trigger_runs
 */
class Run extends Has_Condition\Run
{

	//--------------------------------------------------------------------------------------- $change
	/**
	 * @composite
	 * @var Change
	 */
	public $change;

}
