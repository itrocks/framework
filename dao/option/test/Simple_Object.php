<?php
namespace ITRocks\Framework\Dao\Option\Test;

use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Class Simple_Object
 */
class Simple_Object
{

	//----------------------------------------------------------------------------------------- $date
	public Date_Time $date;

	//----------------------------------------------------------------------------------------- $name
	public string $name;

	//----------------------------------------------------------------------------------- $sub_object
	#[Getter, Store(false)]
	public object $sub_object;

	//---------------------------------------------------------------------------------- getSubObject
	/**
	 * @noinspection PhpUnused #Getter
	 */
	public function getSubObject() : object
	{
		return new Simple_Object();
	}

}
