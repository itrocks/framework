<?php
namespace SAF\Framework\RAD;

use SAF\Framework\Dao\File;
use SAF\Framework\Mapper;

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
	use Mapper\Component;

	//----------------------------------------------------------------------------------- Application
	/**
	 * @composite
	 * @mandatory
	 * @var Application
	 */
	public $application;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @mandatory
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
