<?php
namespace SAF\Framework\RAD\Plugins;

use SAF\Framework\Application;
use SAF\Framework\Mapper\Component;
use SAF\Framework\Dao\File;
use SAF\Framework\RAD\Feature;
use SAF\Framework\RAD\Tag;
use SAF\Framework\Traits\Has_Name;

/**
 * A plugin is a set of code which has limited dependencies to others plugins
 *
 * This class gather the ideas of highly application involved modules and limited dependencies
 * plugins.
 *
 * @representative name
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
