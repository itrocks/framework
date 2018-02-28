<?php
namespace ITRocks\Framework\Configuration\File;

/**
 * If the configuration file works with a single addToConfiguration call per read line
 */
interface Has_Add_To_Configuration
{

	//---------------------------------------------------------------------------- addToConfiguration
	/**
	 * @param $line string
	 */
	function addToConfiguration($line);

}
