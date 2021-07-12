<?php
namespace ITRocks\Framework\Dao\Option\Test;

use ITRocks\Framework\Tools\Date_Time;

/**
 * Class Simple_Object
 */
class Simple_Object
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @var Date_Time
	 */
	public $date;

	//----------------------------------------------------------------------------------- $sub_object
	/**
	 * @store false
	 * @getter
	 * @see getSubObject
	 * @var object
	 */
	public $sub_object;

	//---------------------------------------------------------------------------------- getSubObject
	/**
	 * @return object
	 */
	public function getSubObject() : object
	{
		return new Simple_Object();
	}

}