<?php
namespace SAF\Framework\RAD;

/**
 * A tag is a keyword the facilitate searches
 *
 * @set RAD_Tags
 */
class Tag
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

}
