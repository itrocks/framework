<?php
namespace ITRocks\Framework\Examples;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Examples\Car\Element;
use ITRocks\Framework\Traits\Has_Creation_Date_Time;
use ITRocks\Framework\Traits\Has_Name;

/**
 * An example car class
 *
 * @display_order name, creation, description, elements, image, video
 * @group _top name
 * @group Description description, elements
 * @group Media image, video
 * @group Info creation
 * @store_name example_cars
 */
class Car
{
	use Has_Creation_Date_Time;
	use Has_Name;

	//---------------------------------------------------------------------------------- $description
	/**
	 * @multiline
	 * @var string
	 */
	public $description;

	//------------------------------------------------------------------------------------- $elements
	/**
	 * @link Map
	 * @var Element[]
	 */
	public $elements;

	//---------------------------------------------------------------------------------------- $image
	/**
	 * @var File
	 */
	public $image;

	//---------------------------------------------------------------------------------------- $video
	/**
	 * @var File
	 */
	public $video;

}
