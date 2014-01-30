<?php
namespace SAF\Framework;

/**
 * Aop dealer
 *
 * Execute Aop links or store them until class is loaded
 */
class Aop_Dealer implements Activable_Plugin
{

	//---------------------------------------------------------------------------------------- $links
	/**
	 * @var array
	 */
	private $links = array();

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		foreach (array_keys($this->links) as $class_name) {
			if (class_exists($class_name, false) || trait_exists($class_name, false)) {
				$this->includedClass($class_name, true);
			}
		}
	}

	//--------------------------------------------------------------------------------- includedClass
	/**
	 * @param $class_name string
	 * @param $result     boolean for Aop use. don't fill it for manual calls
	 */
	public function includedClass($class_name, $result = true)
	{
		if ($result) {
			if (isset($this->links[$class_name])) {
				foreach ($this->links[$class_name] as $link) {
					list($method, $joinpoint, $advice) = $link;
					Aop::$method($joinpoint, $advice);
				}
			}
		}
	}

	//------------------------------------------------------------------------------- afterMethodCall
	/**
	 * @param $joinpoint string[]
	 * @param $advice    callable
	 */
	public function afterMethodCall($joinpoint, $advice)
	{
		if (class_exists($joinpoint[0], false) || trait_exists($joinpoint[0], false)) {
			Aop::addAfterMethodCall($joinpoint, $advice);
		}
		$this->links[$joinpoint[0]][] = array("addAfterMethodCall", $joinpoint, $advice);
	}

	//------------------------------------------------------------------------------ aroundMethodCall
	/**
	 * @param $joinpoint string[]
	 * @param $advice    callable
	 */
	public function aroundMethodCall($joinpoint, $advice)
	{
		if (class_exists($joinpoint[0], false) || trait_exists($joinpoint[0], false)) {
			Aop::addAroundMethodCall($joinpoint, $advice);
		}
		$this->links[$joinpoint[0]][] = array("addAroundMethodCall", $joinpoint, $advice);
	}

	//------------------------------------------------------------------------------ beforeMethodCall
	/**
	 * @param $joinpoint string[]
	 * @param $advice    callable
	 */
	public function beforeMethodCall($joinpoint, $advice)
	{
		if (class_exists($joinpoint[0], false) || trait_exists($joinpoint[0], false)) {
			Aop::addBeforeMethodCall($joinpoint, $advice);
		}
		$this->links[$joinpoint[0]][] = array("addBeforeMethodCall", $joinpoint, $advice);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Plugin_Register
	 */
	public function register(Plugin_Register $register)
	{
		$dealer = $register->dealer;
		$dealer->afterMethodCall(
			array('SAF\Framework\Autoloader', "includeClass"), array($this, "includedClass")
		);
	}

}
