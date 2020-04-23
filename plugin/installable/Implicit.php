<?php
namespace ITRocks\Framework\Plugin\Installable;

use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Reflection\Annotation\Class_\Extends_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Feature_Annotation;
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class Reflection_Class|string
	 */
	public function __construct($class)
	{
		if (is_string($class)) {
			/** @noinspection PhpUnhandledExceptionInspection $class must be valid */
			$class = new Reflection_Class($class);
		}
		$this->class = $class;
		if ($class->isTrait() && Extends_Annotation::notOf($class, Extends_Annotation::STRICT)->value) {
			$this->type = T_TRAIT;
		}
		else {
			$this->type = T_CLASS;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval(Feature_Annotation::of($this->class)->value);
	}

	//--------------------------------------------------------------------------------------- install
	/**
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
		$installer->addPlugin($this->class->name);
	}

		//---------------------------------------------------------------------------------- installTrait
	/**
	 * @param $installer Installer
	 */
	protected function installTrait(Installer $installer)
	{
		$extends_annotations = Extends_Annotation::allNotOf($this->class, Extends_Annotation::STRICT);
		foreach ($extends_annotations as $annotations) {
			foreach ($annotations->declared_class_names as $extends) {
				$installer->addToClass($extends, $this->class->name);
			}
		}
	}

}
