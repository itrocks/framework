<?php
namespace SAF\Framework;

/**
 * Aop dealer
 *
 * Execute Aop links or store them until class is loaded
 */
class Aop_Dealer implements Plugin
{

	//-------------------------------------------------------------------------------------- $classes
	/**
	 * @var string[]
	 */
	public $classes = array();

	//---------------------------------------------------------------------------------------- $links
	/**
	 * @var array
	 */
	public $links = array();

	//--------------------------------------------------------------------------------- includedClass
	/**
	 * @param $class_name string
	 * @param $result     boolean for Aop use. don't fill it for manual calls
	 */
	public function includedClass($class_name, $result = true)
	{
		if ($result) {
			$this->classes[$class_name] = true;
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
		if (isset($this->classes[$joinpoint[0]])) {
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
		if (isset($this->classes[$joinpoint[0]])) {
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
		if (isset($this->classes[$joinpoint[0]])) {
			Aop::addBeforeMethodCall($joinpoint, $advice);
		}
		$this->links[$joinpoint[0]][] = array("addBeforeMethodCall", $joinpoint, $advice);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $dealer     Aop_Dealer
	 * @param $parameters array
	 */
	public function register($dealer, $parameters)
	{
		foreach (get_declared_classes() as $class_name) {
			$this->classes[$class_name] = true;
		}
		foreach (get_declared_interfaces() as $interface_name) {
			$this->classes[$interface_name] = true;
		}
		foreach (get_declared_traits() as $trait_name) {
			$this->classes[$trait_name] = true;
		}
		Aop::addAfterMethodCall(
			array('SAF\Framework\Autoloader', "includeClass"), array($this, "includedClass")
		);
	}

}
