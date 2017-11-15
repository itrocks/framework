<?php
namespace ITRocks\Framework\RAD\Plugins;

use ITRocks\Framework\Application;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Dao\File;
use ITRocks\Framework\RAD\Feature;
use ITRocks\Framework\RAD\Tag;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A plugin is a set of code which has limited dependencies to others plugins
 *
 * This class gather the ideas of highly application involved modules and limited dependencies
 * plugins.
 *
 * @representative name
 * @store_name rad_plugins
 */
class Plugin
{
	use Component;
	use Has_Name;

	//---------------------------------------------------------------------------------- $application
	/**
	 * @composite
	 * @link Object
	 * @mandatory
	 * @store string
	 * @var Application
	 */
	public $application;

	//-------------------------------------------------------------------------------------- $summary
	/**
	 * @textile
	 * @var string
	 */
	public $summary;

	//---------------------------------------------------------------------------------- $description
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

	//------------------------------------------------------------------------------------- $children
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

	//------------------------------------------------------------------------------------- $features
	/**
	 * @link Collection
	 * @var Feature[]
	 */
	public $features;

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * @composite
	 * @link Object
	 * @var Plugin
	 */
	public $parent;

	//----------------------------------------------------------------------------------------- $tags
	/**
	 * @link Map
	 * @var Tag[]
	 */
	public $tags;

}
