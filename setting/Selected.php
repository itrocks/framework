<?php
namespace ITRocks\Framework\Setting;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Setting;

/**
 * A Setting\Custom\Set object with the information of it is selected or not
 *
 * Used for custom settings list views
 */
class Selected
{

	//------------------------------------------------------------------------------------- $selected
	/**
	 * @mandatory
	 * @var boolean
	 */
	public bool $selected;

	//-------------------------------------------------------------------------------------- $setting
	/**
	 * @mandatory
	 * @var Setting
	 */
	public Setting $setting;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a selected setting
	 *
	 * @param $setting Setting
	 * @param $selected boolean
	 */
	public function __construct(Setting $setting, bool $selected = false)
	{
		$this->setting  = $setting;
		$this->selected = $selected;
	}

	//-------------------------------------------------------------------------------------------- id
	/**
	 * @return mixed
	 */
	public function id() : mixed
	{
		return Dao::getObjectIdentifier($this->setting);
	}

	//--------------------------------------------------------------------------------- selectedClass
	/**
	 * @return string
	 */
	public function selectedClass() : string
	{
		return $this->selected ? 'selected' : '';
	}

}
