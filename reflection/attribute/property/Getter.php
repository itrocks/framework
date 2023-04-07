<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Attribute\Always;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Default_Callable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Get_Default_Arguments;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ReflectionException;

/**
 * Tells a method name that is the getter for that property.
 *
 * The getter will be called each time the program accesses the property.
 * When there is a @link annotation and no #Getter, a defaut #Getter is set with the Dao access
 * common method depending on the link type.
 */
#[Always, Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Getter implements Has_Get_Default_Arguments, Has_Set_Final
{
	use Common;
	use Has_Default_Callable;

	//------------------------------------------------------------------------------------------ LINK
	protected const LINK = '¤link¤';

	//--------------------------------------------------------------------------- getDefaultArguments
	public static function getDefaultArguments(Reflection $reflection) : array
	{
		return [static::LINK];
	}

	//-------------------------------------------------------------------------------------- setFinal
	/** @throws ReflectionException */
	public function setFinal(Reflection|Reflection_Property $reflection) : void
	{
		if (($this->callable[1] ?? false) === static::LINK) {
			$link           = Link_Annotation::of($reflection)->value;
			$this->callable = $link ? [Mapper\Getter::class, 'get' . $link] : [];
			return;
		}
		$this->getDefaultMethod('get', $reflection);
	}

}
