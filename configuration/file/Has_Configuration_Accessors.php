<?php
namespace ITRocks\Framework\Configuration\File;

/**
 * If the configuration file works with a single addToConfiguration call per read line
 */
interface Has_Configuration_Accessors
{

	//---------------------------------------------------------------------------- addToConfiguration
	/**
	 * @param $line string
	 */
	function addToConfiguration($line);

	//------------------------------------------------------------------------- getConfigurationLines
	/**
	 * @return string[]
	 */
	function getConfigurationLines();

}
