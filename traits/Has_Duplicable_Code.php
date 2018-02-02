<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Dao;

/**
 * Has_Duplicable_Code
 *
 * @duplicate duplicateCode
 */
trait Has_Duplicable_Code
{

	use Has_Code;

	//--------------------------------------------------------------------------------- duplicateCode
	public function duplicateCode()
	{
		// remove '-COPY-X' from code
		$this->code = lLastParse($this->code, '-COPY') . '-COPY';
		/** @var $counter string ie '-2' */
		$counter = '';
		while (Dao::searchOne(['code' => $this->code . $counter], get_class($this))) {
			$counter = $counter
				? lLastParse($counter, '-') . '-' . (intval(rLastParse($counter, '-')) + 1)
				: '-2';
		}
		// append '(copy)' to name
		$this->code .= $counter;
	}

}
