<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Class_\Extend;

/**
 * Standard renaming on duplicate
 *
 * @duplicate duplicateCode
 */
#[Extend(Duplicate_Discriminate_By_Counter::class, Has_Code::class)]
trait Has_Code_Duplicate
{

	//--------------------------------------------------------------------------------- duplicateCode
	public function duplicateCode() : void
	{
		/** @var $this Duplicate_Discriminate_By_Counter|self */
		$this->duplicateDiscriminateByCounter('code');
	}

}
