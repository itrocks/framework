<?php
namespace ITRocks\Framework\RAD;

/**
 * Final user installable feature
 *
 * @display_order title, description, tags, application_class_name
 * @representative title
 * @store_name rad_features
 */
class Feature
{

	//----------------------------------------------------------------------- $application_class_name
	/**
	 * @user invisible
	 * @var string
	 */
	public $application_class_name;

	//---------------------------------------------------------------------------------- $description
	/**
	 * @max_length 64000
	 * @multiline
	 * @var string
	 */
	public $description;

	//---------------------------------------------------------------------------- $plugin_class_name
	/**
	 * @user invisible
	 * @var string
	 */
	public $plugin_class_name;

	//----------------------------------------------------------------------------------------- $tags
	/**
	 * @link Map
	 * @var Tag[]
	 */
	public $tags;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * @mandatory
	 * @var string
	 */
	public $title;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $title       string Feature title
	 * @param $description string Feature complete description
	 */
	public function __construct($title = null, $description = null)
	{
		if (isset($title))       $this->title       = $title;
		if (isset($description)) $this->description = $description;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->title);
	}

}
