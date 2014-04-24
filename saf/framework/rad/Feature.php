<?php
namespace SAF\Framework\RAD;

/**
 * RAD Feature class
 *
 * @set RAD_Features
 */
class Feature
{

	//---------------------------------------------------------------------------------------- $title
	/**
	 * @var string
	 */
	public $title;

	//---------------------------------------------------------------------------------- $description
	/**
	 * @var string
	 */
	public $description;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $identifier    string the feature identifier. eg must be the main class of the feature
	 * @param $title         string plugin title
	 * @param $description   string plugin description
	 * @param $configuration array the plugins configuration : key is the plugin class name
	 */
	public function __construct(
		$identifier, $title = null, $description = null, $configuration = null
	) {
		if (isset($title))         $this->title       = $title;
		if (isset($description))   $this->description = $description;
		if (isset($configuration)) $this->configuration = $configuration;
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
