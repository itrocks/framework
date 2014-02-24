<?php
namespace SAF\Framework\RAD;

use SAF\Framework;

/**
 * A plugin component is used to store data or process, ie linked to a database
 */
class Component
{
	use Framework\Component;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//---------------------------------------------------------------------------------------- $rules
	/**
	 * @ling Collection
	 * @var Rule[]
	 */
	public $rules;

}
