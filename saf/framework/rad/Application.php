<?php
namespace SAF\Framework\RAD;

/**
 * Application
 *
 * A set of plugins
 *
 * @set RAD_Applications
 */
class Application
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @mandatory
	 * @var string
	 */
	public $name;

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * @var Application
	 */
	public $parent;

	//-------------------------------------------------------------------------------------- $plugins
	/**
	 * @link Collection
	 * @var Plugin[]
	 */
	public $plugins;

}
