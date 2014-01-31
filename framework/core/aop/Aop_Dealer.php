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
	 * @var object[]|string[]
	 */
	private $serialized_objects = array();

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		// each time a new class will be created, then will include class
		Aop::addAfterMethodCall(
			array('SAF\Framework\Autoloader', "includeClass"), array($this, "includedClass")
		);
		// call Aop links for existing classes and traits
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
					// unserialize advice object
					if (is_array($advice) && is_string($reference = $advice[0]) && ($reference[1] == ":")) {
						$object = $this->serialized_objects[$reference];
						if (is_string($object)) {
							$object = ($object[1] == ":")
								? unserialize($object)
								: Session::current()->getPlugin($object);
						}
						$advice[0] = $object;
						$this->links[$class_name][$key][2][0] = $object;
						$this->serialized_objects[$reference] = $object;
					}
					// call Aop link
					Aop::$method($joinpoint, $advice);
				}
			}
		}
		// get instance and activate plugin
		if (is_a($class_name, 'SAF\Framework\Plugin', true)) {
			Session::current()->getPlugin($class_name, false);
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
	 * @todo it's too long, cut this in several functions
	 */
	public function serialize()
	{
		// get already serialized objects references
		$new_references     = array();
		$reference_counter  = 0;
		$serialized_objects = array();
		$serialized_plugins = array();
		foreach ($this->serialized_objects as $reference => $object) {
			// serialize a plugin : only keep its class name (session will serialize it)
			if ($reference[2] == "p") {
				$serialized = is_object($object) ? get_class($object) : $object;
				$reference = $new_references[$reference] = "r:p" . $reference_counter++;
				$serialized_plugins[$serialized] = $reference;
			}
			// serialize an object and get its reference
			elseif (is_object($object)) {
				$serialized = serialize($object);
				$reference  = serialize($object);
			}
			// an already serialized object : change its reference
			else {
				$serialized = $object;
				$reference  = $new_references[$reference] = "r:i" . $reference_counter++;
			}
			// store the serialized object
			$serialized_objects[$reference] = $serialized;
		}
		// serialize objects and store their reference
		foreach ($this->links as $class_name => $sub_links) {
			foreach ($sub_links as $key => $link) {
				// if advice is a callable array($object|"class","method")
				if (is_array($link[2])) {
					$object = $link[2][0];
					// callable object : serialize and store reference
					if (is_object($object)) {
						// plugins are serialized at session level, we store only a reference here
						if ($object instanceof Plugin) {
							$plugin_class = get_class($object);
							if (isset($serialized_plugins[$plugin_class])) {
								$serialized = $serialized_plugins[$plugin_class];
							}
							else {
								$reference = "r:p" . $reference_counter++;
								$serialized_objects[$reference] = $plugin_class;
								$serialized_plugins[$plugin_class] = $reference;
								$serialized = $reference;
							}
						}
						// serialize a non-plugin object
						else {
							$serialized = serialize($object);
							if ($serialized[0] != "r") {
								$reference = serialize($object);
								$serialized_objects[$reference] = $serialized;
								$serialized = $reference;
							}
						}
						$this->links[$class_name][$key][2][0] = $serialized;
					}
					// callable serialized object : update the reference
					elseif ($object[1] == ":") {
						$this->links[$class_name][$key][2][0] = $new_references[$object];
					}
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
