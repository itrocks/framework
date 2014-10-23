<?php
namespace SAF\Framework\RAD\Applications\Instance;

use SAF\Framework\Mapper;
use SAF\Framework\RAD\Applications\Instance\Rule_Setting;
use SAF\Framework\RAD\Plugins\Plugin;

/**
 * Active plugin : a plugin that is used into an application instance context
 *
 * @link Plugin
 * @representative plugin.name
 */
class Active_Plugin extends Plugin
{
	use Mapper\Component;

	//------------------------------------------------------------------------- $application_instance
	/**
	 * @composite
	 * @link Object
	 * @var Instance
	 */
	public $application_instance;

	//--------------------------------------------------------------------------------------- $plugin
	/**
	 * @composite
	 * @link Object
	 * @var Plugin
	 */
	private /* @noinspection PhpUnusedPrivateFieldInspection @link */ $plugin;

	//---------------------------------------------------------------------------------------- $rules
	/**
	 * @link Collection
	 * @var Rule_Setting[]
	 */
	public $rules;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->plugin->name);
	}

}
