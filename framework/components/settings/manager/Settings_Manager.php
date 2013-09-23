<?php
namespace SAF\Framework;

/**
 * The settings class, in order to manage application settings
 */
class Settings_Manager
{

	//------------------------------------------------------------------------------------ $templates
	/**
	 * @link All
	 * @var Settings_Template[]
	 */
	public $templates;

	//--------------------------------------------------------------------------------------- $groups
	/**
	 * @var Settings_Groups
	 */
	public $groups;

	//----------------------------------------------------------------------------------- __construct
	/**
	 */
	public function __construct()
	{
		if (!isset($this->groups)) {
			$this->groups = new Settings_Groups();
		}
	}

}
