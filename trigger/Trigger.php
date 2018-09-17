<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Trigger\Action;
use ITRocks\Framework\Widget\Edit\Widgets\Map_As_Collection;

/**
 * A trigger calculates if an action must be run
 *
 * @business
 */
abstract class Trigger
{

	//-------------------------------------------------------------------------------------- $actions
	/**
	 * @link Map
	 * @see Map_As_Collection
	 * @var Action[]
	 * @widget Map_As_Collection
	 */
	public $actions;

}
