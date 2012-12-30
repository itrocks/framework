<?php
namespace SAF\Framework;

class Code
{

	//----------------------------------------------------------------------------------------- $code
	/**
	 * @var string
	 */
	public $code;

	//---------------------------------------------------------------------------------- $description
	/**
	 * @var string
	 */
	public $description;

	//------------------------------------------------------------------------------------ __toString
	public function __toString()
	{
		return trim($this->code . " " . $this->description);
	}

}
