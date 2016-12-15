<?php
namespace ITRocks\Framework\Plugin;

use ITRocks\Framework\Plugin;

/**
 * The constructor of a configurable plugin must accept the configuration array as unique parameter
 */
interface Configurable extends Plugin
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 *
	 * @param $configuration array
	 */
	public function __construct($configuration);

}
