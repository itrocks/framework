<?php
namespace SAF\Framework\RAD\Plugin;

use SAF\Framework\Mapper;
use SAF\Framework\RAD\Rule_Setting;
use SAF\Framework\RAD\Application_Instance;
use SAF\Framework\RAD\Plugin;

/**
 * Active plugin : a plugin that is used into an application instance context
 *
 * @link Plugin
 * @set RAD_Active_Plugins
 */
class Active_Plugin extends Plugin
{
	use Mapper\Component;

	//------------------------------------------------------------------------- $application_instance
	/**
	 * @composite
	 * @link Object
	 * @var Application_Instance
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

}
