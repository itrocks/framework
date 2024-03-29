<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Class_\Extend;

/**
 * Standard renaming on duplicate
 *
 * @duplicate duplicateName
 */
#[Extend(Duplicate_Discriminate_By_Counter::class, Has_Name::class)]
trait Has_Name_Duplicate
{

	//--------------------------------------------------------------------------------- duplicateName
	/**
	 * @noinspection PhpUnused @duplicate
	 */
	public function duplicateName() : void
	{
		/** @var $this Duplicate_Discriminate_By_Counter|self */
		$this->duplicateDiscriminateByCounter('name');
	}

}
