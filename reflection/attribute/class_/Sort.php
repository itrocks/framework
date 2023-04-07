<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection\Annotation\Property;
use ITRocks\Framework\Reflection\Attribute\Always;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Attribute\Template\Is_List;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * The sort annotation for classes stores a list of column names for object collections sort
 *
 * This is used by Dao to get default sort orders when calling Dao::readAll() and Dao::search().
 * This work like Class_Representative_Annotation : default values are the complete properties list
 */
#[Always, Attribute(Attribute::TARGET_CLASS), Inheritable]
class Sort implements Has_Set_Final
{
	use Common;
	use Is_List;

	//-------------------------------------------------------------------------------------- setFinal
	/** Default representative is the list of non-static properties of the class */
	public function setFinal(Reflection|Reflection_Class $reflection) : void
	{
		if ($this->values) {
			return;
		}
		$representative = Representative::of($reflection)->values;
		foreach ($representative as $property_path) {
			/** @noinspection PhpUnhandledExceptionInspection class and property must be valid */
			$property = new Reflection_Property($reflection->getName(), $property_path);
			if (
				!$property->isStatic()
				&& (!Property\Link_Annotation::of($property)->value || Store::of($property)->isString())
			) {
				$this->values[] = $property_path;
			}
		}
	}

}
