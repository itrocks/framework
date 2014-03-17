<?php
namespace SAF\Framework\RAD;

class Dependency
{

	//------------------------------------------------------------------------------------------- $id
	/**
	 * @var string
	 */
	private $id;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $id string
	 */
	public function __construct($id = null)
	{
		if (isset($id)) $this->id = $id;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->id;
	}

}
