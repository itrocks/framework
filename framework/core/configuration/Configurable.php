<?php
namespace SAF\Framework;

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
