<?php
namespace SAF\Framework;

/**
 * Standard basic codes, with a code and a description
 */
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
	/**
	 * @return string
	 */
	public function __toString()
	{
		return trim($this->code . " " . $this->description);
	}

}
