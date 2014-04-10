<?php
namespace SAF\Framework\Plugin;

use SAF\Framework\AOP\Weaver\IWeaver;

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
	public function __construct($configuration = null, IWeaver $aop = null)
	{
		if (isset($aop))           $this->aop           = $aop;
		if (isset($configuration)) $this->configuration = $configuration;
	}

	//------------------------------------------------------------------------------ getConfiguration
	/**
	 * @return array|string
	 */
	/* @noinspection PhpUnusedPrivateMethodInspection @getter */
	private function getConfiguration()
	{
		if (!$this->get) {
			if (!is_array($this->configuration)) {
				$this->configuration = isset($this->configuration) ? [$this->configuration => true] : [];
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
	/* @noinspection PhpUnusedPrivateMethodInspection @setter */
	private function setConfiguration($configuration)
	{
		$this->configuration = $configuration;
		$this->get = false;
	}

}
