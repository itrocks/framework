<?php
namespace SAF\Framework;

/**
 * Plugin register structure
 */
class Plugin_Register
{

	//-------------------------------------------------------------------------------- $configuration
	/**
	 * @var array|string
	 */
	private $configuration;

	//--------------------------------------------------------------------------------------- $dealer
	/**
	 * @var Aop_Dealer
	 */
	public $dealer;

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
	 * @param $dealer        Aop_Dealer
	 */
	public function __construct($configuration = null, Aop_Dealer $dealer = null)
	{
		if (isset($configuration)) $this->configuration = $configuration;
		if (isset($dealer))        $this->dealer        = $dealer;
		$this->get = false;
	}

	//------------------------------------------------------------------------------ getConfiguration
	/**
	 * @return array
	 */
	public function getConfiguration()
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
	public function setConfiguration($configuration)
	{
		$this->configuration = $configuration;
		$this->get = false;
	}

}
