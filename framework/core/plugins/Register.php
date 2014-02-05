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
	/* public */ private function __construct_($configuration = null, IWeaver $aop = null)
	{
		if (isset($aop))           $this->aop           = $aop;
		if (isset($configuration)) $this->configuration = $configuration;
	}

	//------------------------------------------------------------------------------ getConfiguration
	/**
	 * @return array|string
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
		// overridden / parent call here
		$this->__construct_($configuration, $aop);
	}

	/**
	 * @param $property_name string
	 * @return mixed
	 */
	public function __get($property_name)
	{
		if ($property_name[0] == '_') {
			// overridden / parent call here, without '_'
			user_error(
				'Undefined property: Plugin_Register::$' . substr($property_name, 1), E_USER_NOTICE
			);
			return null;
		}
		$_property_name = '_' . $property_name;
		$this->$property_name = $this->$_property_name;
		if ($property_name == 'configuration') {
			$value = $this->getConfiguration();
		}
		else {
			// overridden / parent call here
			user_error('Undefined property: Plugin_Register::$' . $property_name, E_USER_NOTICE);
			$value = null;
		}
		$this->$_property_name = $this->$property_name;
		unset($this->$property_name);
		return $value;
	}

	/**
	 * @param $property_name string
	 * @return boolean
	 */
	public function __isset($property_name)
	{
		if ($property_name[0] == '_') {
			// overridden / parent call here, without '_'
			return isset($this->$property_name);
		}
		$_property_name = '_' . $property_name;
		return isset($this->$_property_name);
	}

	/**
	 * @param $property_name string
	 * @param $value         mixed
	 */
	public function __set($property_name, $value)
	{
		if ($property_name[0] == '_') {
			// overridden / parent call here, without '_'
			$this->$property_name = $value;
		}
		elseif ($property_name == 'configuration') {
			$this->setConfiguration($value);
		}
		else {
			$this->$property_name = $value;
		}
	}

	/**
	 * @param $property_name string
	 */
	public function __unset($property_name)
	{
		if ($property_name[0] == '_') {
			// overridden / parent call here, without '_'
			unset($property_name);
			return;
		}
		$property_name = '_' . $property_name;
		unset($this->$property_name);
	}

}
