<?php
namespace SAF\Framework\Print_Model;

use SAF\Framework\Mapper\Component;

/**
 * A print model zone is a zone into the page that contains links to the data to be printed
 */
class Zone
{
	use Component;

	//----------------------------------------------------------------------------------------- $page
	/**
	 * The link to the page which contains the zone
	 *
	 * @composite
	 * @link Object
	 * @var Page
	 */
	public $page;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * The name of the zone
	 *
	 * @var string
	 */
	public $name;

	//--------------------------------------------------------------------------------------- $zoning
	/**
	 * Raw zoning data (json)
	 *
	 * @max_length 1000000
	 * @var string
	 */
	public $zoning;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->page) . SP . strval($this->name);
	}

}
