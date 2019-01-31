<?php
namespace ITRocks\Framework\Dao\Mysql;

/**
 * An object representation of a mysql view
 */
class View
{

	//----------------------------------------------------------------------------------------- $Name
	/**
	 * @var string
	 */
	private $Name;

	//------------------------------------------------------------------------------- $select_queries
	/**
	 * @var string[]
	 */
	public $select_queries = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name string
	 */
	public function __construct($name = null)
	{
		if (isset($name)) {
			$this->Name = $name;
		}
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->Name;
	}

}
