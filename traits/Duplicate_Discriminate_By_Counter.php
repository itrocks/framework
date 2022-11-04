<?php
namespace ITRocks\Framework\Traits;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;

/**
 * Adds a method that enables to discriminate
 */
trait Duplicate_Discriminate_By_Counter
{

	//---------------------------------------------------------------- duplicateDiscriminateByCounter
	/**
	 * @param $property_name string
	 */
	protected function duplicateDiscriminateByCounter(string $property_name)
	{
		// remove '-COPY-X' from the value
		$copy = Loc::tr('COPY');
		$this->$property_name = lLastParse($this->$property_name, '-' . $copy) . '-' . $copy;
		$counter = '';
		while (Dao::searchOne([$property_name => $this->$property_name . $counter], get_class($this))) {
			$counter = $counter
				? lLastParse($counter, '-') . '-' . (intval(rLastParse($counter, '-')) + 1)
				: '-2';
		}
		// append '(copy)' to name
		$this->$property_name .= $counter;
	}

}
