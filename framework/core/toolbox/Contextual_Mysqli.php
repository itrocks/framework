<?php
namespace SAF\Framework;
use mysqli;

/**
 * Contextual mysqli class : this enables storage of context name for mysqli queries calls
 */
class Contextual_Mysqli extends mysqli
{

	//-------------------------------------------------------------------------------------- $context
	/**
	 * Query execution context
	 *
	 * @var string
	 */
	public $context;

}
