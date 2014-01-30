<?php
namespace SAF\Framework;

use Serializable;

/**
 * Aop dealer
 *
 * Execute Aop links or store them until class is loaded
 */
class Aop_Dealer implements Activable_Plugin, Serializable
{

	//---------------------------------------------------------------------------------------- $links
	/**
	 * @var array
	 */
	private $links = array();

	//--------------------------------------------------------------------------- $serialized_objects
	/**
	 * @var string[]
	 */
	private $serialized_objects = array();

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		Aop::addAfterMethodCall(
			array('SAF\Framework\Autoloader', "includeClass"), array($this, "includedClass")
		);
		foreach (array_keys($this->links) as $class_name) {
			if (class_exists($class_name, false) || trait_exists($class_name, false)) {
				$this->includedClass($class_name, true);
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

	//--------------------------------------------------------------------------------- includedClass
	/**
	 * @param $class_name string
	 * @param $result     boolean for Aop use. don't fill it for manual calls
	 */
	public function includedClass($class_name, $result = true)
	{
		if ($result) {
			if (isset($this->links[$class_name])) {
				foreach ($this->links[$class_name] as $key => $link) {
					list($method, $joinpoint, $advice) = $link;
					if (is_array($advice) && is_string($advice[0]) && ($advice[0][1] == ":")) {
						$advice[0] = unserialize($this->serialized_objects[$advice[0]]);
						$this->links[$class_name][$key][2][0] = $advice[0];
					}
					Aop::$method($joinpoint, $advice);
				}
			}
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Plugin_Register
	 */
	public function register(Plugin_Register $register)
	{
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * @return string
	 */
	public function serialize()
	{
		$serialized_objects = array();
		foreach ($this->links as $class_name => $sub_links) {
			foreach ($sub_links as $key => $link) {
				if (is_array($link[2]) && is_object($link[2][0])) {
					$serialized = serialize($link[2][0]);
					if ($serialized[0] != "r") {
						$reference = serialize($link[2][0]);
						$serialized_objects[$reference] = $serialized;
						$serialized = $reference;
					}
					$this->links[$class_name][$key][2][0] = $serialized;
				}
			}
		}
		return serialize(array($this->links, $serialized_objects));
	}

	//----------------------------------------------------------------------------------- unserialize
	/**
	 * @param string $serialized
	 */
	public function unserialize($serialized)
	{
		list($this->links, $this->serialized_objects) = unserialize($serialized);
	}

}
