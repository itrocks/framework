<?php
namespace ITRocks\Framework\Plugin\Installable;

use ITRocks\Framework\Plugin;
use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Plugin\Priority;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * Implicit installable plugin
 *
 * This is a default feature / plugin class to allow installation of a plugin simply declared with
 * the @feature class annotation
 */
class Implicit implements Installable
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class
	 */
	public $class;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @values T_TRAIT
	 * @var integer
	 */
	public $type;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class Reflection_Class|string
	 */
	public function __construct($class)
	{
		if (is_string($class)) {
			$class = new Reflection_Class($class);
		}
		$this->class = $class;
		if ($class->isTrait() && ($class->getListAnnotation('extends')->value)) {
			$this->type = T_TRAIT;
		}
		elseif ($class->isA(Plugin::class, [T_EXTENDS, T_IMPLEMENTS, T_USE])) {
			$this->type = T_CLASS;
		}
		else {
			trigger_error(
				"Does not know how class $class->name could be installed as a plugin",
				E_USER_ERROR
			);
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * Returns the short description of the installable plugin
	 *
	 * It is the feature caption exactly how it will be displayed to the user
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->class->getAnnotation('feature')->value;
	}

	//--------------------------------------------------------------------------------------- install
	/**
	 * This code is called when the plugin is installed by the user
	 *
	 * Use the $installer parameter to install the components of your plugin.
	 *
	 * @param $installer Installer
	 */
	public function install(Installer $installer)
	{
		switch ($this->type) {
			case T_CLASS: $this->installClass($installer); break;
			case T_TRAIT: $this->installTrait($installer); break;
		}
	}

	//---------------------------------------------------------------------------------- installClass
	/**
	 * @param $installer Installer
	 */
	protected function installClass(Installer $installer)
	{
		$installer->addPlugin(Priority::NORMAL, $this->class->name);
	}

		//---------------------------------------------------------------------------------- installTrait
	/**
	 * @param $installer Installer
	 */
	protected function installTrait(Installer $installer)
	{
		foreach ($this->class->getListAnnotation('extends')->values() as $extends) {
			$installer->addToClass($extends, $this->class->name);
		}
	}

}
