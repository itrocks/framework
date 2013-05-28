<?php
namespace SAF\Framework;

/**
 * Configurable classes can get php array configuration to be passed to their constructor
 */
interface Configurable
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Configurable classes must have a constructor with an array of parameters
	 *
	 * @param $parameters array
	 */
	public function __construct($parameters = null);

}
