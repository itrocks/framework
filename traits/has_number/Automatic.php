<?php
namespace ITRocks\Framework\Traits\Has_Number;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Data_Link\Identifier_Map;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Objects\Counter;
use ITRocks\Framework\Traits\Has_Number;
use ITRocks\Framework\View\View_Exception;

/**
 * @after_write incrementNumber
 * @override number @calculated @mandatory false @user readonly
 */
trait Automatic
{
	use Has_Number;

	//------------------------------------------------------------------------------- incrementNumber
	/**
	 * This calculates $number if it is empty, using the Counter which identifier is the class name
	 *
	 * The job is done after the document has been written : if any problem occurs, we should not
	 * have incremented the counter, and there is more luck to have problem before than after write
	 *
	 * @param $link Data_Link
	 * @throws View_Exception
	 */
	public function incrementNumber(Data_Link $link)
	{
		if (empty($this->number)) {
			if (($link instanceof Identifier_Map) && $link->getObjectIdentifier($this)) {
				$this->number = Counter::increment($this);
				$link->write($this, Dao::only('number'));
			}
			else {
				throw new View_Exception(Loc::tr('number') . ' : ' . Loc::tr('mandatory'));
			}
		}
	}

}
