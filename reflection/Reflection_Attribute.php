<?php
namespace ITRocks\Framework\Reflection;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Get_Default_Arguments;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Declaring;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Declaring_Class;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Reflection_Attribute;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

/**
 * Enriches ReflectionAttribute with :
 * - additional methods devoted to keep trace of the declaring and final Reflection objects
 * - newInstances propagates declaring and final  Reflection objects
 * - Reflection_Attribute with a named attribute can be used to instantiate the default attribute,
 *   when not explicitly declared
 */
class Reflection_Attribute
{

	//------------------------------------------------------------------------------------ $attribute
	protected ?ReflectionAttribute $attribute;
	
	//------------------------------------------------------------------------------------ $declaring
	protected Reflection $declaring;

	//------------------------------------------------------------------------------ $declaring_class
	protected ?Interfaces\Reflection_Class $declaring_class;

	//---------------------------------------------------------------------------------------- $final
	protected Reflection $final;

	//------------------------------------------------------------------------------------- $instance
	protected ?object $instance = null;

	//--------------------------------------------------------------------------------- $is_attribute
	protected bool $is_attribute;
	
	//------------------------------------------------------------------------------- $is_inheritable
	protected bool $is_inheritable;

	//-------------------------------------------------------------------------------- $is_repeatable
	protected bool $is_repeatable;

	//----------------------------------------------------------------------------------------- $name
	protected string $name;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(
		object|string $attribute, Reflection $declaring, Reflection $final,
		?Interfaces\Reflection_Class $declaring_class
	) {
		if (is_string($attribute)) {
			$this->attribute = null;
			$this->name      = $attribute;
		}
		elseif ($attribute instanceof ReflectionAttribute) {
			$this->attribute = $attribute;
			$this->name      = $attribute->getName();
		}
		else {
			$this->attribute = null;
			$this->instance  = $attribute;
			$this->name      = get_class($attribute);
			// TODO useless when Override(..., new Mandatory) is automatically replaced by Builder::create
			$class_name      = Builder::className($this->name);
			if (!is_a($this->instance, $class_name, true)) {
				/** @noinspection PhpUnhandledExceptionInspection replacement class name is always valid */
				$this->instance = Builder::createClone($this->instance, $class_name);
			}
		}
		$this->declaring       = $declaring;
		$this->declaring_class = $declaring_class;
		$this->final           = $final;
	}

	//---------------------------------------------------------------------------------- getArguments
	public function getArguments() : array
	{
		return $this->attribute?->getArguments() ?: [];
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	public function getDeclaringClass(bool $trait = true) : ?Interfaces\Reflection_Class
	{
		return $trait
			? (($this->declaring instanceof Interfaces\Reflection_Class) ? $this->declaring : null)
			: $this->declaring_class;
	}

	//-------------------------------------------------------------------------- getDeclaringProperty
	public function getDeclaringProperty() : ?Reflection_Property
	{
		return ($this->declaring instanceof Reflection_Property) ? $this->declaring : null;
	}

	//--------------------------------------------------------------------------------- getFinalClass
	public function getFinalClass() : Interfaces\Reflection_Class
	{
		return ($this->final instanceof Interfaces\Reflection_Class)
			? $this->final
			: $this->final->getFinalClass();
	}

	//------------------------------------------------------------------------------ getFinalProperty
	public function getFinalProperty() : ?Reflection_Property
	{
		return ($this->final instanceof Reflection_Property)
			? $this->final
			: null;
	}

	//--------------------------------------------------------------------------------------- getName
	public function getName() : string
	{
		return $this->name;
	}

	//------------------------------------------------------------------------------------- getTarget
	public function getTarget() : int
	{
		return $this->attribute?->getTarget() ?: 0;
	}

	//--------------------------------------------------------------------------------- isInheritable
	public function isInheritable() : bool
	{
		if (isset($this->is_inheritable)) {
			return $this->is_inheritable;
		}
		return $this->is_inheritable = class_exists($this->name)
			&& (new ReflectionClass($this->name))->getAttributes(Inheritable::class);
	}
	
	//---------------------------------------------------------------------------------- isRepeatable
	public function isRepeatable() : bool
	{
		if (isset($this->is_repeatable)) {
			return $this->is_repeatable;
		}
		$this->is_repeatable = class_exists($this->name)
			&& ($attribute = (new ReflectionClass($this->name))->getAttributes(\Attribute::class))
			&& ($attribute[0]->newInstance()->flags & \Attribute::IS_REPEATABLE);
		return $this->is_repeatable;
	}

	//------------------------------------------------------------------------------------ isRepeated
	public function isRepeated() : bool
	{
		return $this->attribute?->isRepeated() ?: false;
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * @throws ReflectionException
	 */
	public function newInstance($default = false) : object
	{
		if ($this->instance) {
			$object = $this->instance;
		}
		else {
			$name   = $this->name;
			$object = ($default && is_a($name, Has_Get_Default_Arguments::class, true))
				? Builder::create($name, $name::getDefaultArguments())
				: Builder::create($name, $this->getArguments());
		}
		if ($object instanceof Has_Set_Declaring) {
			$object->setDeclaring($this->declaring);
		}
		if ($object instanceof Has_Set_Declaring_Class) {
			$object->setDeclaringClass($this->declaring_class);
		}
		if ($object instanceof Has_Set_Final) {
			$object->setFinal($this->final);
		}
		if ($object instanceof Has_Set_Reflection_Attribute) {
			$object->setReflectionAttribute($this);
		}
		$this->instance = $object;
		return $object;
	}

}
