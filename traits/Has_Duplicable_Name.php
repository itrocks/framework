<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;

/**
 * Trait Has_Duplicable_Name
 *
 * @duplicate duplicateName
 */
trait Has_Duplicable_Name
{
	use Has_Name;

	//--------------------------------------------------------------------------------- duplicateName
	public function duplicateName()
	{
		$pattern_copy = SP . '(' . Loc::tr('copy');
		$name = lLastParse($this->name, $pattern_copy) . $pattern_copy;
		$count_copies = Dao::count(['name' => "$name%"], get_class($this));
		// append pattern copy to name
		$this->name .= $pattern_copy . SP . (intval($count_copies + 1). ')');
	}

}
