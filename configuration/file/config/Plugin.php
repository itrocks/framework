<?php
namespace ITRocks\Framework\Configuration\File\Config;

/**
 * config.php plugin configuration
 */
class Plugin
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * The plugin configuration full class name, without ::class
	 *
	 * @var string
	 */
	public string $class_name;

	//-------------------------------------------------------------------------------- $configuration
	/**
	 * The plugin configuration, as raw string
	 *
	 * This is the right part of the => into the configuration file, without the first space nor
	 * the trailing ,
	 *
	 * @var string
	 */
	public string $configuration;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->class_name;
	}

}
