<?php
namespace SAF\Framework;

use SAF\Framework\Configuration\Environment;

/**
 * A configuration set : current configuration for the global application configuration, secondary configurations can be worked with
 */
class Configuration
{

	//------------------------------------------------------------------------------------------- APP
	const APP = 'app';

	//---------------------------------------------------------------------------------------- AUTHOR
	const AUTHOR = 'author';

	//------------------------------------------------------------------------------------ CLASS_NAME
	const CLASS_NAME = 'class';

	//----------------------------------------------------------------------------------- ENVIRONMENT
	const ENVIRONMENT = 'environment';

	//----------------------------------------------------------------------------------- EXTENDS_APP
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

	//---------------------------------------------------------------------------------- $environment
	/**
	 * @values development, production, test
	 * @var string
	 */
	public $environment;

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
		$this->application_class = $configuration[self::APP];
		$this->author            = $configuration[self::AUTHOR];
		$this->environment       = isset($configuration[self::ENVIRONMENT])
			? $configuration[self::ENVIRONMENT]
			: Environment::DEVELOPMENT;
		$this->name = $name;
		unset($configuration[self::APP]);
		unset($configuration[self::AUTHOR]);
		unset($configuration[self::ENVIRONMENT]);
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
