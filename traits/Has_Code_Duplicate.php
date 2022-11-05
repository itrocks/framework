<?php
namespace ITRocks\Framework\Traits;

/**
 * Standard renaming on duplicate
 *
 * @duplicate duplicateCode
 * @extends Duplicate_Discriminate_By_Counter
 * @extends Has_Code
 */
trait Has_Code_Duplicate
{

	//--------------------------------------------------------------------------------- duplicateCode
	public function duplicateCode() : void
	{
		/** @var $this Duplicate_Discriminate_By_Counter|self */
		$this->duplicateDiscriminateByCounter('code');
	}

}
