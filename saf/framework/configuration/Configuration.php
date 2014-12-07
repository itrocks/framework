<?php
namespace SAF\Framework;

/**
 * A configuration set : current configuration for the global application configuration, secondary configurations can be worked with
 */
class Configuration
{

	//------------------------------------------------------------ Configuration array keys constants
	const APP = 'app';
	const AUTHOR = 'author';
	const CLASS_NAME = 'class';
	const EXTENDS_APP = 'extends';

	//---------------------------------------------------------------------------- $application_class
	/**
	 * Application class name
	 *
	 * @var string
	 */
	private $application_class;

	//--------------------------------------------------------------------------------------- $author
	/**
	 * @var string
	 */
	private $author;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * Configuration name
	 *
	 * @var string
	 */
	private $name;

	//-------------------------------------------------------------------------------------- $plugins
	/**
	 * @var array
	 */
	private $plugins;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Build configuration using configurations options
	 *
	 * Default configuration is set to the configuration if the 'default' option is set to true.
	 *
	 * @param $name          string application name
	 * @param $configuration array recursive configuration array from the config.php file
	 */
	public function __construct($name, $configuration)
	{
		$this->name = $name;
		$this->application_class = $configuration[self::APP];
		$this->author = $configuration[self::AUTHOR];
		unset($configuration[self::APP]);
		unset($configuration[self::AUTHOR]);
		$this->plugins = $configuration;
	}

	//----------------------------------------------------------------------- getApplicationClassName
	/**
	 * @example 'SAF\Tests\Application'
	 * @return string
	 */
	public function getApplicationClassName()
	{
		if (!isset($this->application_class)) {
			$this->application_class = (isset($this->author) ? $this->author : 'SAF') . BS
				. $this->name . '\Application';
		}
		return $this->application_class;
	}

	//---------------------------------------------------------------------------- getApplicationName
	/**
	 * Get the configuration's application name
	 *
	 * @example 'tests'
	 * @return string
	 */
	public function getApplicationName()
	{
		return $this->name;
	}

	//------------------------------------------------------------------------------------ getPlugins
	/**
	 * @return array
	 */
	public function getPlugins()
	{
		return $this->plugins;
	}

}
