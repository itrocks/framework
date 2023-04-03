<?php
namespace ITRocks\Framework\Setting;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Setting;

/**
 * A Setting\Custom\Set object with the information of it is selected or not
 *
 * Used for custom settings list views
 */
class Selected
{

	//------------------------------------------------------------------------------------- $selected
	#[Mandatory]
	public bool $selected;

	//-------------------------------------------------------------------------------------- $setting
	#[Mandatory]
	public Setting $setting;

	//----------------------------------------------------------------------------------- __construct
	/** Constructs a selected setting */
	public function __construct(Setting $setting, bool $selected = false)
	{
		$this->setting  = $setting;
		$this->selected = $selected;
	}

	//-------------------------------------------------------------------------------------------- id
	public function id() : mixed
	{
		return Dao::getObjectIdentifier($this->setting);
	}

	//--------------------------------------------------------------------------------- selectedClass
	public function selectedClass() : string
	{
		return $this->selected ? 'selected' : '';
	}

}
