<?php
namespace SAF\Framework;

class Configuration
{

	//------------------------------------------------------------------------------------------ $app
	/**
	 * @var array
	 */
	private $app;

	//-------------------------------------------------------------------------------------- $current
	/**
	 * @var Configuration
	 */
	private static $current;

	//------------------------------------------------------------------------------------------ $dao
	/**
	 * @var array
	 */
	private $dao;

	//-------------------------------------------------------------------------------------- $default
	/**
	 * @var boolean
	 */
	private $default = false;

	//----------------------------------------------------------------------------------------- $view
	/**
	 * @var array
	 */
	private $view;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Build configuration using configurations options
	 *
	 * Default configuration is set to the configuration if the "default" option is set to true.
	 *
	 * @param array $configuration_options recursive configuration array from the config.php file 
	 */
	public function __construct($configuration_options)
	{
		foreach ($configuration_options as $name => $value) {
			$this->$name = $value;
		}
		if ($this->default) {
			Configuration::setCurrent($this);
		}
	}

	//---------------------------------------------------------------------------- getApplicationName
	/**
	 * Get the configuration's application name
	 *
	 * @return string
	 */
	public function getApplicationName()
	{
		return $this->app;
	}

	//------------------------------------------------------------------------------------ getCurrent
	/**
	 * Get the current configuration object, as set by default into the configuration file or by setCurrent()
	 *
	 * @return Configuration
	 */
	public static function getCurrent()
	{
		return Configuration::$current;
	}

	//---------------------------------------------------------------------------------------- getDao
	/**
	 * Get the DAO configuration as a recursive array
	 *
	 * @return array
	 */
	public function getDao()
	{
		return $this->dao;
	}

	//------------------------------------------------------------------------------- getDaoClassName
	/**
	 * Get the DAO class name : the DAO "class" option, with the "_Link" standard DAO suffix 
	 *
	 * @return string
	 */
	public function getDaoClassName()
	{
		return $this->dao["class"] . "_Link";
	}

	//--------------------------------------------------------------------------------- getViewEngine
	/**
	 * Get the view engine configuration as a recursive array
	 *
	 * @return array
	 */
	public function getViewEngine()
	{
		return $this->view;
	}

	//------------------------------------------------------------------------ getViewEngineClassName
	/**
	 * Get the view engine class name : the View "engine" option, with the "_View_Engine" standard view engine suffix
	 *
	 * @return string
	 */
	public function getViewEngineClassName()
	{
		return $this->view["engine"] . "_View_Engine";
	}

	//------------------------------------------------------------------------------------ setCurrent
	/**
	 * Set the current configuration to the given Configuration object
	 *
	 * @param Configuration $configuration
	 */
	public static function setCurrent($configuration)
	{
		Configuration::$current = $configuration;
	}

}
