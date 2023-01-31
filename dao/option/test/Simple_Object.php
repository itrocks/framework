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
	public string $name;

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @var Date_Time
	 */
	public Date_Time $date;

	//----------------------------------------------------------------------------------- $sub_object
	/**
	 * @getter
	 * @store false
	 * @var object
	 */
	public object $sub_object;

	//---------------------------------------------------------------------------------- getSubObject
	/**
	 * @noinspection PhpUnused @getter
	 * @return object
	 */
	public function getSubObject() : object
	{
		return new Simple_Object();
	}

}
