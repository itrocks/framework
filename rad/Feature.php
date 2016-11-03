<?php
namespace ITRocks\Framework\RAD;

use ITRocks\Framework\Mapper\Component;
use /** @noinspection PhpUnusedAliasInspection @widget */
	ITRocks\Framework\Widget\Edit\Widgets\Collection_As_Map;

/**
 * RAD Feature class
 *
 * @representative title, type
 * @set RAD_Features
 */
class Feature
{
	use Component;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @values application, application instance, class, feature, form, framework, module, plugin,
	 * print, process, root class, rule, trait, view
	 * @var string
	 */
	public $type;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * @mandatory
	 * @var string
	 */
	public $title;

	//-------------------------------------------------------------------------------------- $summary
	/**
	 * @multiline
	 * @var string
	 * @wiki
	 */
	public $summary;

	//---------------------------------------------------------------------------------- $description
	/**
	 * @multiline
	 * @var string
	 * @wiki
	 */
	public $description;

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * @link Object
	 * @var Feature
	 */
	public $parent;

	//------------------------------------------------------------------------------------- $children
	/**
	 * @link Collection
	 * @var Feature[]
	 * @widget Collection_As_Map
	 */
	public $children;

	//----------------------------------------------------------------------------------------- $tags
	/**
	 * @link Map
	 * @var Tag[]
	 */
	public $tags;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $title         string Feature title
	 * @param $summary       string Feature short summary
	 * @param $description   string Feature complete description
	 * @param $configuration array the plugins configuration : key is the plugin class name
	 */
	public function __construct(
		$title = null, $summary = null, $description = null, $configuration = null
	) {
		if (isset($title))         $this->title         = $title;
		if (isset($summary))       $this->summary       = $summary;
		if (isset($description))   $this->description   = $description;
		if (isset($configuration)) $this->configuration = $configuration;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->title . ($this->type ? (SP . '(' . $this->type . ')') : '');
	}

	//--------------------------------------------------------------------------------------- depends
	/**
	 * @param $identifier string the ignored feature identifier
	 * @return Dependency
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
