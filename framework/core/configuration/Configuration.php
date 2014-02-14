<?php
namespace SAF\Framework;

/**
 * A configuration set : current configuration for the global application configuration, secondary configurations can be worked with
 */
class Configuration
{

	//----------------------------------------------------------------------------- $application_name
	/**
	 * @var string
	 */
	private $application_name;

	//----------------------------------------------------------------------- $application_class_name
	/**
	 * @var string
	 */
	private $application_class_name;

	//--------------------------------------------------------------------------------------- $author
	/**
	 * @var string
	 */
	private $author;

	//-------------------------------------------------------------------------------------- $plugins
	/**
	 * @var array
	 */
	private $plugins;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Build configuration using configurations options
	 *
	 * Default configuration is set to the configuration if the "default" option is set to true.
	 *
	 * @param $configuration array recursive configuration array from the config.php file
	 */
	public function __construct($configuration)
	{
		$this->application_name = $configuration["app"];
		$this->author = $configuration["author"];
		unset($configuration["app"]);
		unset($configuration["author"]);
		$this->plugins = $configuration;
	}

	//----------------------------------------------------------------------- getApplicationClassName
	/**
	 * @return string
	 */
	public function getApplicationClassName()
	{
		if (!isset($this->application_class_name)) {
			$this->application_class_name = (isset($this->author) ? $this->author : "SAF") . "\\"
				. $this->application_name . "\\Application";
		}
		return $this->application_class_name;
	}

	//---------------------------------------------------------------------------- getApplicationName
	/**
	 * Get the configuration's application name
	 *
	 * @return string
	 */
	public function getApplicationName()
	{
		return $this->application_name;
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
