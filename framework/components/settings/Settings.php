<?php
namespace SAF\Framework;

class Settings
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
	public function __construct()
	{
		if (!isset($this->groups)) {
			$this->groups = new Settings_Groups();
		}
	}

}
