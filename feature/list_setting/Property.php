<?php
namespace ITRocks\Framework\Feature\List_Setting;

use ITRocks\Framework\Setting;

/**
 * Data list setting widget for a property (ie a column of the list)
 */
class Property extends Setting\Property
{

	//------------------------------------------------------------------------------------- $group_by
	/**
	 * @var boolean
	 */
	public bool $group_by = false;

	//----------------------------------------------------------------------------------- htmlGroupBy
	/**
	 * @noinspection PhpUnused Property_edit.html
	 * @return string
	 */
	public function htmlGroupBy() : string
	{
		return $this->group_by ? 'checked' : '';
	}

}
