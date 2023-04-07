<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Always;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Attribute\Template\Is_List;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

/**
 * Class attribute #List [List_::LOCK], property1[, property2[, etc]]
 *
 * Indicates which property we want by default for the list controller on the class
 * If true is passed, the user can not customize its list by adding / removing columns
 */
#[Always, Attribute(Attribute::TARGET_CLASS), Inheritable]
class List_ implements Has_Set_Final
{
	use Common;
	use Is_List { __construct as parentConstruct; }

	//------------------------------------------------------------------------------------------ LOCK
	const LOCK = true;

	//----------------------------------------------------------------------------------------- $lock
	public bool $lock = false;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(mixed ...$values)
	{
		foreach ($values as $key => $value) {
			if (is_bool($value)) {
				$this->lock = $value;
				unset($values[$key]);
			}
		}
		$this->parentConstruct(...$values);
	}

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Reflection|Reflection_Class $reflection) : void
	{
		if ($this->values) {
			return;
		}
		$this->values = Representative::of($reflection)->values;
	}

}
