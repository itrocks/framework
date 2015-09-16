<?php
namespace SAF\Framework\RAD\Plugins;

use SAF\Framework\Dao\File;
use SAF\Framework\Mapper;
use SAF\Framework\RAD\Applications\Application;
use SAF\Framework\RAD\Features\Feature;
use SAF\Framework\RAD\Tag;

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
	use Mapper\Component;

	//---------------------------------------------------------------------------------- $application
	/**
	 * @composite
	 * @link Object
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

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

}
