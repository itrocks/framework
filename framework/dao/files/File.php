<?php
namespace SAF\Framework;

/**
 * File is a simple business object that stores files
 */
class File
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @var string
	 * @max-length 4000000000
	 */
	public $content;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

}
