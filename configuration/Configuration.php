<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Configuration\Environment;

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

	//---------------------------------------------------------------------------------------- DOMAIN
	const DOMAIN = 'domain';

	//----------------------------------------------------------------------------------- ENVIRONMENT
	const ENVIRONMENT = 'environment';

	//----------------------------------------------------------------------------------- EXTENDS_APP
	const EXTENDS_APP = 'extends';

	//--------------------------------------------------------------------------- TEMPORARY_DIRECTORY
	const TEMPORARY_DIRECTORY = 'temporary_directory';

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

	//--------------------------------------------------------------------------------------- $domain
	/**
	 * Domain name (optional) eg for applications that generate dynamic URL or generic emails
	 *
	 * @var string
	 */
	public $domain;

	//---------------------------------------------------------------------------------- $environment
	/**
	 * @values development, production, test
	 * @var string
	 */
	public $environment;

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * Configuration file name, when configuration was loaded from a file
	 *
	 * @var string|null
	 */
	public $file_name = null;

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

	//-------------------------------------------------------------------------- $temporary_directory
	/**
	 * @var string
	 */
	public $temporary_directory;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Build configuration using configurations options
	 *
	 * Default configuration is set to the configuration if the 'default' option is set to true.
	 *
	 * @param $name          string application name
	 * @param $configuration array recursive configuration array from the config.php file
	 */
	public function __construct($name, array $configuration)
	{
		$this->application_class = $configuration[self::APP];
		$this->author            = $configuration[self::AUTHOR];
		$this->domain            = isset($configuration[self::DOMAIN])
			? $configuration[self::DOMAIN]
			: 'itrocks.org';
		$this->environment = isset($configuration[self::ENVIRONMENT])
			? $configuration[self::ENVIRONMENT]
			: Environment::DEVELOPMENT;
		$this->name = $name;
		$this->temporary_directory = isset($configuration[self::TEMPORARY_DIRECTORY])
			? $configuration[self::TEMPORARY_DIRECTORY]
			: null;
		unset($configuration[self::APP]);
		unset($configuration[self::AUTHOR]);
		unset($configuration[self::DOMAIN]);
		unset($configuration[self::ENVIRONMENT]);
		unset($configuration[self::TEMPORARY_DIRECTORY]);
		$this->plugins = $configuration;
	}

	//----------------------------------------------------------------------- getApplicationClassName
	/**
	 * @example 'ITRocks\Tests\Application'
	 * @return string
	 */
	public function getApplicationClassName()
	{
		if (!isset($this->application_class)) {
			$this->application_class
				= (isset($this->author) ? $this->author : 'ITRocks') . BS . $this->name . BS . 'Application';
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
