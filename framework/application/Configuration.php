<?php
namespace Framework;

class Configuration
{

	/**
	 * @var array
	 */
	private $app;

	/**
	 * @var Configuration
	 */
	private static $current;

	/**
	 * @var array
	 */
	private $dao;

	/**
	 * @var boolean
	 */
	private $default = false;

	/**
	 * @var array
	 */
	private $view;

	//----------------------------------------------------------------------------------- __construct
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
	 * @return string
	 */
	public function getApplicationName()
	{
		return $this->app;
	}

	//------------------------------------------------------------------------------------ getCurrent
	/**
	 * @return Configuration
	 */
	public static function getCurrent()
	{
		return Configuration::$current;
	}

	//---------------------------------------------------------------------------------------- getDao
	/**
	 * @return array
	 */
	public function getDao()
	{
		return $this->dao;
	}

	//------------------------------------------------------------------------------- getDaoClassName
	/**
	 * @return string
	 */
	public function getDaoClassName()
	{
		return $this->dao["class"] . "_Link";
	}

	//--------------------------------------------------------------------------------- getViewEngine
	/**
	 * @return array
	 */
	public function getViewEngine()
	{
		return $this->view;
	}

	//------------------------------------------------------------------------ getViewEngineClassName
	/**
	 * @return string
	 */
	public function getViewEngineClassName()
	{
		return $this->view["engine"] . "_View_Engine";
	}

	//------------------------------------------------------------------------------------ setCurrent
	/**
	 * @param Configuration $configuration
	 */
	public static function setCurrent($configuration)
	{
		Configuration::$current = $configuration;
	}

}
