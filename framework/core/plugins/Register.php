<?php
namespace SAF\Plugins;

use SAF\AOP\IWeaver;

/**
 * Plugin register structure
 */
class Register
{

	//------------------------------------------------------------------------------------------ $aop
	/**
	 * @var IWeaver
	 */
	public $aop;

	//-------------------------------------------------------------------------------- $configuration
	/**
	 * @getter getConfiguration
	 * @setter setConfiguration
	 * @var array|string
	 */
	public $configuration;

	//------------------------------------------------------------------------------------------ $get
	/**
	 * @var boolean
	 */
	private $get;

	//---------------------------------------------------------------------------------------- $level
	/**
	 * @values core, highest, higher, high, normal, low, lower, lowest
	 * @var string
	 */
	public $level;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array|string
	 * @param $aop           IWeaver
	 */
	public function __construct_($configuration = null, IWeaver $aop = null)
	{
		if (isset($aop))           $this->aop           = $aop;
		if (isset($configuration)) $this->configuration = $configuration;
	}

	//------------------------------------------------------------------------------ getConfiguration
	/**
	 * @return array
	 */
	private function getConfiguration()
	{
		if (!$this->get) {
			if (!is_array($this->configuration)) {
				$this->configuration = isset($this->configuration)
					? array($this->configuration => true)
					: array();
			}
			foreach ($this->configuration as $key => $value) {
				if (is_numeric($key) && is_string($value)) {
					unset($this->configuration[$key]);
					$this->configuration[$value] = true;
				}
			}
			$this->get = true;
		}
		return $this->configuration;
	}

	//------------------------------------------------------------------------------ setConfiguration
	/**
	 * @param $configuration array|string
	 */
	private function setConfiguration($configuration)
	{
		$this->configuration = $configuration;
		$this->get = false;
	}

	//########################################################################################### AOP

	/**
	 * @param $configuration array|string
	 * @param $aop           IWeaver
	 */
	public function __construct($configuration = null, IWeaver $aop = null)
	{
		$this->_configuration = $this->configuration;
		unset($this->configuration);
		$this->__construct_($configuration, $aop);
	}

	/**
	 * @param $property_name string
	 * @return mixed
	 */
	public function __get($property_name)
	{
		switch ($property_name) {
			case 'configuration':
				$value = $this->getConfiguration();
				return $value;
		}
		user_error('Undefined property: Plugin_Register::$' . $property_name, E_USER_NOTICE);
		return null;
	}

	/**
	 * @param $property_name string
	 * @return boolean
	 */
	public function __isset($property_name)
	{
		switch ($property_name) {
			case 'configuration':
				return isset($this->_configuration);
		}
		return isset($this->$property_name);
	}

	/**
	 * @param $property_name string
	 * @param $value         mixed
	 */
	public function __set($property_name, $value)
	{
		switch ($property_name) {
			case 'configuration':
				$this->setConfiguration($value);
				return;
		}
		$this->$property_name = $value;
		return;
	}

	/**
	 * @param $property_name string
	 */
	public function __unset($property_name)
	{
		switch ($property_name) {
			case 'configuration':
				unset($this->_configuration);
				return;
		}
		unset($this->$property_name);
		return;
	}

}
