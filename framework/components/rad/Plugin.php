<?php
namespace SAF\Framework\RAD;

/**
 * A plugin is a set of code that can be used on several application contexts
 *
 * It does not depend on any application nor module, but it can need other plugins
 */
class Plugin
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//--------------------------------------------------------------------------------- $dependencies
	/**
	 * @link Map
	 * @var Plugin[]
	 */
	public $dependencies;

	//--------------------------------------------------------------------------------- $data_classes
	/**
	 * @link Collection
	 * @var Data_Class[]
	 */
	public $data_classes;

}
