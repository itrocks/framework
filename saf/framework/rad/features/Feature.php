<?php
namespace SAF\Framework\RAD\Features;

use SAF\Framework\Mapper;
use SAF\Framework\RAD\Components\Component;
use SAF\Framework\RAD\Dependency;
use SAF\Framework\RAD\Plugins\Plugin;

/**
 * RAD Feature class
 *
 * @representative title
 */
class Feature
{
	use Mapper\Component;

	//--------------------------------------------------------------------------------------- $plugin
	/**
	 * @composite
	 * @link Object
	 * @var Plugin
	 */
	public $plugin;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * @mandatory
	 * @var string
	 */
	public $title;

	//---------------------------------------------------------------------------------- $description
	/**
	 * @var string
	 */
	public $description;

	//----------------------------------------------------------------------------------- $components
	/**
	 * @link Collection
	 * @var Component[]
	 */
	public $components;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $identifier    string
	 * @param $title         string plugin title
	 * @param $description   string plugin description
	 * @param $configuration array the plugins configuration : key is the plugin class name
	 */
	public function __construct(
		$identifier = null, $title = null, $description = null, $configuration = null
	) {
		if (isset($title))         $this->title         = $title;
		if (isset($description))   $this->description   = $description;
		if (isset($configuration)) $this->configuration = $configuration;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->title);
	}

	//--------------------------------------------------------------------------------------- depends
	/**
	 * @param $identifier string the ignored feature identifier
	 * @return Ignored_Feature
	 */
	public static function depends($identifier)
	{
		return new Dependency($identifier);
	}

	//---------------------------------------------------------------------------------------- ignore
	/**
	 * @param $identifier string the ignored feature identifier
	 * @return Ignored_Feature
	 */
	public static function ignore($identifier)
	{
		return new Ignored_Feature($identifier);
	}

}
