<?php
namespace ITRocks\Framework\Traits;

/**
 * Standard renaming on duplicate
 *
 * @duplicate duplicateName
 * @extends Duplicate_Discriminate_By_Counter
 * @extends Has_Name
 */
trait Has_Name_Duplicate
{

	//--------------------------------------------------------------------------------- duplicateName
	public function duplicateName()
	{
		/** @var $this Duplicate_Discriminate_By_Counter|self */
		$this->duplicateDiscriminateByCounter('name');
	}

}
