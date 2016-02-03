<?php
namespace SAF\Framework\User\Group;

/**
 * Low level features cache to keep into session / file / anywhere you want
 */
class Low_Level_Features_Cache
{

	//------------------------------------------------------------------------------------- $features
	/**
	 * @var Low_Level_Feature[]
	 */
	public $features;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $features Low_Level_Feature[]
	 */
	public function __construct($features = null)
	{
		if (isset($features)) {
			$this->features = $features;
		}
	}

}
