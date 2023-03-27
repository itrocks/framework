<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_PROPERTY), Inheritable]
class User_Change extends Property implements Has_Set_Final
{

	//------------------------------------------------------------------------------- $change_feature
	/**
	 * @var string[]
	 */
	public array $change_feature;

	//------------------------------------------------------------------------------------- $realtime
	public bool $realtime;

	//--------------------------------------------------------------------------------------- $target
	public string $target;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $change_feature string|string[] 'featureName' or [Class_Name::class, 'featureName']
	 * @param $realtime       boolean
	 * @param $target         string
	 */
	public function __construct(
		array|string $change_feature, bool $realtime = false, string $target = ''
	) {
		$this->change_feature = is_array($change_feature) ? $change_feature : ['', $change_feature];
		$this->realtime       = $realtime;
		$this->target         = $target;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return join('::', $this->change_feature) . ($this->target ? ('>' . $this->target) : '');
	}

	//------------------------------------------------------------------------------------ asHtmlData
	/**
	 * @param $object ?object The reference object, if set
	 * @return string
	 */
	public function asHtmlData(?object $object) : string
	{
		$identifier                  = $object ? Dao::getObjectIdentifier($object) : null;
		[$class_name, $feature_name] = $this->change_feature;
		$class_name                 = Builder::current()->sourceClassName($class_name);
		return str_replace(BS, SL, $class_name)
			. ($identifier ? (SL . $identifier) : '')
			. SL . $feature_name
			. ($this->target ? (SP . $this->target) : '');
	}

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Reflection|Reflection_Property $reflection) : void
	{
		if (reset($this->change_feature)) return;
		$this->change_feature[key($this->change_feature)] = $reflection->getFinalClassName();
	}

}
