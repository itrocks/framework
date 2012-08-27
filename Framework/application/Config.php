<?php

class Config
{

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @var string
	 */
	private $current;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($config = null)
	{
		if ($config) {
			$this->config = array($config);
			$this->current = reset($this->config);
		} else {
			$this->load();
		}
	}

	//-------------------------------------------------------------------------------- getApplication
	public function getApplication()
	{
		return $this->current["app"];
	}

	//----------------------------------------------------------------------------- getConfigurations
	public function getConfigurations()
	{
		return $this->config;
	}

	//------------------------------------------------------------------------------------ getCurrent
	public function getCurrent()
	{
		return $this->config[$this->current];
	}

	//---------------------------------------------------------------------------------------- getDao
	public function getDao()
	{
		return $this->config[$this->current]["dao"];
	}

	//----------------------------------------------------------------------------------- getDaoClass
	public function getDaoClassName()
	{
		return $this->config[$this->current]["dao"]["class"] . "_Link";
	}

	//--------------------------------------------------------------------------------------- getView
	public function getView()
	{
		return $this->config[$this->current]["view"];
	}

	//---------------------------------------------------------------------------------- getViewClass
	public function getViewClass()
	{
		return $this->getView() . "_View";
	}

	//------------------------------------------------------------------------------------------ load
	/**
	 * @return Config;
	 */
	public function load()
	{
		include "config.php";
		$this->config  = $config;
		$this->current = $this->config["default"]; 
		return $this;
	}

	//---------------------------------------------------------------------------------------- select
	public function select($config)
	{
		$this->current = $config;
	}

}
