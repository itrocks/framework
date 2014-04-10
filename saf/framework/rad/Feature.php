<?php
namespace SAF\Framework\RAD;

/**
 * RAD Feature class
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
	 * @param $id            string the feature identifier. eg must be the main class of the feature
	 * @param $title         string plugin title
	 * @param $description   string plugin description
	 * @param $configuration array the plugins configuration : key is the plugin class name
	 */
	public function __construct($id, $title = null, $description = null, $configuration = null)
	{
		if (isset($title))         $this->title       = $title;
		if (isset($description))   $this->description = $description;
		if (isset($configuration)) $this->configuration = $configuration;
	}

	//--------------------------------------------------------------------------------------- depends
	/**
	 * @param $id string the ignored feature identifier
	 * @return Ignored_Feature
	 */
	public static function depends($id)
	{
		return new Dependency($id);
	}

	//---------------------------------------------------------------------------------------- ignore
	/**
	 * @param $id string the ignored feature identifier
	 * @return Ignored_Feature
	 */
	public static function ignore($id)
	{
		return new Ignored_Feature($id);
	}

}
