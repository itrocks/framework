<?php
namespace SAF\Framework;

class Default_Output_Controller_Configuration
{
	use Class_Properties;
	use Current { current as private pCurrent; }

	//----------------------------------------------------------------------------------- __construct
	public function __construct($parameters)
	{
		$this->initClassProperties($parameters);
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Default_Output_Controller_Configuration
	 * @return Default_Output_Controller_Configuration
	 */
	public static function current(Default_Output_Controller_Configuration $set_current = null)
	{
		return self::pCurrent($set_current);
	}

}
