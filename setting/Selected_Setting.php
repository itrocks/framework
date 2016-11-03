<?php
namespace ITRocks\Framework\Setting;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Setting;

/**
 * A Custom_Settings object with the information of it is selected or not
 *
 * Used for custom settings list views
 */
class Selected_Setting
{

	//------------------------------------------------------------------------------------- $selected
	/**
	 * @mandatory
	 * @var boolean
	 */
	public $selected;

	//-------------------------------------------------------------------------------------- $setting
	/**
	 * @mandatory
	 * @var Setting
	 */
	public $setting;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a selected setting
	 *
	 * @param $setting Setting
	 * @param $selected boolean
	 */
	public function __construct(Setting $setting, $selected = false)
	{
		$this->setting  = $setting;
		$this->selected = $selected;
	}

	//-------------------------------------------------------------------------------------------- id
	/**
	 * @return mixed
	 */
	public function id()
	{
		return Dao::getObjectIdentifier($this->setting);
	}

	//--------------------------------------------------------------------------------- selectedClass
	/**
	 * @return string
	 */
	public function selectedClass()
	{
		return $this->selected ? 'selected' : '';
	}

}
