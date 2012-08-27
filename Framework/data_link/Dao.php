<?php

class Dao
{

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * @param string $class_name
	 * @param array  $parameters configuration parameters for constructor calling
	 * @return Data_Link
	 */
	public static function newInstance($class_name, $parameters)
	{
		return new $class_name($parameters);
	}

}
