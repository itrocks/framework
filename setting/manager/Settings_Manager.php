<?php
namespace ITRocks\Framework\Setting\Manager;

/**
 * The settings class, in order to manage application settings
 *
 * @todo Everything
 */
class Settings_Manager
{

	//--------------------------------------------------------------------------------------- $groups
	/**
	 * @var //Settings_Groups
	 */
	public $groups;

	//------------------------------------------------------------------------------------ $templates
	/**
	 * @link All
	 * @var Settings_Template[]
	 */
	public $templates;

	//----------------------------------------------------------------------------------- __construct
	/**
	 */
	public function __construct()
	{
		if (!isset($this->groups)) {
			//$this->groups = new Settings_Groups();
		}
	}

}
