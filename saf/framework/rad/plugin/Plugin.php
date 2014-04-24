<?php
namespace SAF\Framework\RAD;

use SAF\Framework\Dao\File;

/**
 * A plugin is a set of code which has limited dependencies to others plugins
 *
 * This class gather the ideas of highly application involved modules and limited dependencies
 * plugins.
 *
 * @set RAD_Plugins
 */
class Plugin
{

	//--------------------------------------------------------------------------------------- $active
	/**
	 * @var boolean
	 */
	public $active;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//-------------------------------------------------------------------------------------- $summary
	/**
	 * @textile
	 * @var string
	 */
	public $summary;

	//----------------------------------------------------------------------------------------- $logo
	/**
	 * @textile
	 * @var string
	 */
	public $description;

	//----------------------------------------------------------------------------------------- $logo
	/**
	 * @link Object
	 * @var File
	 */
	public $logo;

	//---------------------------------------------------------------------------------- $sub_plugins
	/**
	 * @link Collection
	 * @var Plugin[]
	 */
	public $children;

	//--------------------------------------------------------------------------------- $dependencies
	/**
	 * @link Map
	 * @var Plugin[]
	 */
	public $dependencies;

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * @link Object
	 * @var Plugin
	 */
	public $parent;

	//----------------------------------------------------------------------------------- $components
	/**
	 * @link Collection
	 * @var Component[]
	 */
	public $components;

	//----------------------------------------------------------------------------------------- $tags
	/**
	 * @link Map
	 * @var Tag[]
	 */
	public $tags;

}
