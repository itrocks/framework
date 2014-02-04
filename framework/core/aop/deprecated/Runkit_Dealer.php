<?php
namespace SAF\AOP;

use SAf\Framework\Plugins\Activable;
use Serializable;

/**
 * Aop dealer
 *
 * Execute Aop links or store them until class is loaded
 */
class Runkit_Dealer implements Plugins\Activable, Serializable
{

	//------------------------------------------------------------------------------------------ $has
	/**
	 * @var array
	 */
	private $has;

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

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor : initializes $has array
	 */
	public function __construct()
	{
		$this->has = array(Linker::READ => array(), Linker::WRITE => array());
	}

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		// each time a new class will be created, then will include class
		Linker::addAfterMethodCall(
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
			Linker::addAfterMethodCall($joinpoint, $advice);
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
			Linker::addAroundMethodCall($joinpoint, $advice);
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
			Linker::addBeforeMethodCall($joinpoint, $advice);
		}
		$this->links[$joinpoint[0]][] = array("addBeforeMethodCall", $joinpoint, $advice);
	}

	//-------------------------------------------------------------------------------------- hasLinks
	/**
	 * @param $class_name string
	 * @param $type       string Linker::READ or Linker::WRITE
	 * @return boolean
	 */
	public function hasLinks($class_name, $type = null)
	{
		if (isset($type)) {
			return isset($this->has[$type][$class_name]);
		}
		else {
			return isset($this->links[$class_name]);
		}
	}

	//--------------------------------------------------------------------------------- includedClass
	/**
	 * @param $class_name string
	 * @param $result     boolean for Aop use. don't fill it for manual calls
	 * @param $root_class string force root class and properties only (for internal use only)
	 */
	public function includedClass($class_name, $result = true, $root_class = null)
	{
		if ($result) {
if (class_exists('SAF\Framework\Debug')) Debug::log("includedClass($class_name) START");
			$properties_only = isset($root_class);
			if (isset($this->links[$class_name])) {
				$is_trait = trait_exists($class_name, false);
				foreach ($this->links[$class_name] as $key => $link) {
					list($method, $joinpoint, $advice) = $link;
					$property = (substr($method, 0, 13) == "addOnProperty");
					if ($property || !$properties_only) {
						// prepare for property joinpoint : execute all traits links to the root class
						if ($property && !$is_trait && !isset($done_property)) {
							$done_property = true;
							if (!isset($root_class)) {
								$parents = class_parents($class_name);
								$root_class = $parents ? end($parents) : $class_name;
							}
							// for each parent trait, apply AOP properties links of the trait to the root class
							$check_traits = array($class_name);
							$traits = array();
							while ($check_traits) {
								$will_check = array();
								foreach ($check_traits as $check_name) {
									$parent_traits = class_uses($check_name);
									$will_check    = array_merge($will_check, $parent_traits);
									$traits        = array_merge($traits, $parent_traits);
								}
								$check_traits = $will_check;
							}
							foreach ($traits as $trait_name) {
								$this->includedClass($trait_name, $result, $root_class);
							}
						}
						if (!$property || !$is_trait) {
							// unserialize advice object
							if (
								is_array($advice) && is_string($reference = $advice[0]) && ($reference[1] == ":")
							) {
								$object = $this->serialized_objects[$reference];
								if (is_string($object)) {
									$object = ($object[1] == ":")
										? unserialize($object)
										: Session::current()->plugins->getPlugin($object);
								}
								$advice[0] = $object;
								$this->links[$class_name][$key][2][0] = $object;
								$this->serialized_objects[$reference] = $object;
							}
							// Aop on properties : change joinpoint to root
							if ($property && !$is_trait) {
								$joinpoint[0] = $root_class;
							}
							// make Aop link
							Linker::$method($joinpoint, $advice);
						}
					}
				}
			}
if (class_exists('SAF\Framework\Debug')) Debug::log("includedClass($class_name) DONE");
			// get instance and activate plugin
			if (is_a($class_name, 'SAF\Framework\Plugin', true)) {
				Session::current()->plugins->getPlugin($class_name);
			}
		}
	}

	//-------------------------------------------------------------------------------- onPropertyRead
	/**
	 * @param $joinpoint string[]
	 * @param $advice    callable
	 */
	public function onPropertyRead($joinpoint, $advice)
	{
		$this->has[Linker::READ][$joinpoint[0]] = true;
		$this->links[$joinpoint[0]][] = array("addOnPropertyRead", $joinpoint, $advice);
		// todo default option for immediate link creation
	}

	//------------------------------------------------------------------------------- onPropertyWrite
	/**
	 * @param $joinpoint string[]
	 * @param $advice    callable
	 */
	public function onPropertyWrite($joinpoint, $advice)
	{
		$this->has[Linker::WRITE][$joinpoint[0]] = true;
		$this->links[$joinpoint[0]][] = array("addOnPropertyWrite", $joinpoint, $advice);
		// todo default option for immediate link creation
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
		return serialize(array($this->links, $serialized_objects, $this->has));
	}

	//----------------------------------------------------------------------------------- unserialize
	/**
	 * @param string $serialized
	 */
	public function unserialize($serialized)
	{
		list($this->links, $this->serialized_objects, $this->has) = unserialize($serialized);
	}

}
