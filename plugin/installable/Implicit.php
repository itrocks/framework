<?php
namespace ITRocks\Framework\Plugin\Installable;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Reflection\Annotation\Class_\Feature_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
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
	public Reflection_Class $class;

	//----------------------------------------------------------------------------------------- $type
	#[Values(T_CLASS, T_TRAIT)]
	public int $type;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(Reflection_Class|string $class)
	{
		if (is_string($class)) {
			/** @noinspection PhpUnhandledExceptionInspection $class must be valid */
			$class = new Reflection_Class($class);
		}
		$this->class = $class;
		if ($class->isTrait() && Extend::oneNotOf($class, Extend::STRICT)->extends) {
			$this->type = T_TRAIT;
		}
		else {
			$this->type = T_CLASS;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return strval(Feature_Annotation::of($this->class)->value);
	}

	//--------------------------------------------------------------------------------------- install
	public function install(Installer $installer) : void
	{
		switch ($this->type) {
			case T_CLASS: $this->installClass($installer); break;
			case T_TRAIT: $this->installTrait($installer); break;
		}
	}

	//---------------------------------------------------------------------------------- installClass
	protected function installClass(Installer $installer) : void
	{
		$installer->addPlugin($this->class->name);
	}

	//---------------------------------------------------------------------------------- installTrait
	protected function installTrait(Installer $installer) : void
	{
		$extend_attributes = Extend::notOf($this->class, Extend::STRICT);
		foreach ($extend_attributes as $extend_attribute) {
			foreach ($extend_attribute->extends as $extends) {
				$installer->addToClass(Builder::current()->sourceClassName($extends), $this->class->name);
			}
		}
	}

}
