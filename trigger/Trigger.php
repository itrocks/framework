<?php
namespace ITRocks\Framework;

/**
 * A trigger calculates if an action must be run
 */
abstract class Trigger
{

	//--------------------------------------------------------------------------------------- $action
	/**
	 * An action is an URI that is triggered
	 *
	 * @var string
	 */
	public $action;

}
