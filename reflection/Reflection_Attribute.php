<?php
namespace ITRocks\Framework\Reflection;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Attribute\Local;
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

	//--------------------------------------------------------------------------------- $is_attribute
	protected bool $is_attribute;
	
	//------------------------------------------------------------------------------------- $is_local
	protected bool $is_local;

	//-------------------------------------------------------------------------------- $is_repeatable
	protected bool $is_repeatable;

	//----------------------------------------------------------------------------------------- $name
	protected string $name;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(
		ReflectionAttribute|string $attribute, Reflection $declaring, Reflection $final,
		?Interfaces\Reflection_Class $declaring_class
	) {
		if ($attribute instanceof ReflectionAttribute) {
			$this->attribute = $attribute;
			$this->name      = $attribute->getName();
		}
		else {
			$this->attribute = null;
			$this->name      = $attribute;
		}
		$this->declaring       = $declaring;
		$this->declaring_class = $declaring_class;
		$this->final           = $final;
	}

	//---------------------------------------------------------------------------------- getArguments
	public function getArguments() : array
	{
		return $this->attribute->getArguments() ?? [];
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	public function getDeclaringClass(bool $trait = true) : ?Interfaces\Reflection_Class
	{
		return $trait
			? $this->declaring_class
			: (($this->declaring instanceof Interfaces\Reflection_Class) ? $this->declaring : null);
	}

	//-------------------------------------------------------------------------- getDeclaringProperty
	public function getDeclaringProperty() : ?Reflection_Property
	{
		return ($this->declaring instanceof Reflection_Property) ? $this->declaring : null;
	}

	//--------------------------------------------------------------------------------- getFinalClass
	public function getFinalClass() : ?Interfaces\Reflection_Class
	{
		return ($this->final instanceof Interfaces\Reflection_Class) ? $this->final : null;
	}

	//------------------------------------------------------------------------------ getFinalProperty
	public function getFinalProperty() : ?Reflection_Property
	{
		return ($this->final instanceof Reflection_Property) ? $this->final : null;
	}

	//--------------------------------------------------------------------------------------- getName
	public function getName() : string
	{
		return $this->name;
	}

	//------------------------------------------------------------------------------------- getTarget
	public function getTarget() : int
	{
		return $this->attribute->getTarget() ?? 0;
	}

	//----------------------------------------------------------------------------------- isAttribute
	public function isAttribute() : bool
	{
		if (isset($this->is_attribute)) {
			return $this->is_attribute;
		}
		$this->is_attribute = $this->name
			&& class_exists($this->name)
			&& (new ReflectionClass($this->name))->getAttributes(Attribute::class);
		return $this->is_attribute;
	}

	//--------------------------------------------------------------------------------------- isLocal
	public function isLocal() : bool
	{
		if (isset($this->is_local)) {
			return $this->is_local;
		}
		$this->is_local = class_exists($this->name)
			&& (new ReflectionClass($this->name))->getAttributes(Local::class);
		return $this->is_local;
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
		return $this->attribute->isRepeated() ?? false;
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * @throws ReflectionException
	 */
	public function newInstance($default = false) : object
	{
		$name   = $this->name;
		$object = ($default && method_exists($name, 'getDefaultArguments'))
			? Builder::create($name, $name::getDefaultArguments())
			: ($this->attribute?->newInstance() ?: Builder::create($name));
		if (method_exists($object, 'setReflectionAttribute')) {
			$object->setReflectionAttribute($this);
		}
		if (method_exists($object, 'setDeclaring')) {
			$object->setDeclaring($this->declaring);
		}
		if (method_exists($object, 'setDeclaringClass')) {
			$object->setDeclaringClass($this->declaring_class);
		}
		if (method_exists($object, 'setFinal')) {
			$object->setFinal($this->final);
		}
		return $object;
	}

}
