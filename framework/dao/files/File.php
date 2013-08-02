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
	 * @binary
	 * @max_length 4000000000
	 * @setter setContent
	 * @var string
	 */
	public $content;

	//----------------------------------------------------------------------------------------- $hash
	/**
	 * @var string
	 */
	public $hash;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

	//------------------------------------------------------------------------------------ setContent
	/**
	 * @param $content string
	 */
	public function setContent($content)
	{
		$this->content = $content;
		$this->hash = md5($content);
	}

}
