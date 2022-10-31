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
	private string $Name;

	//------------------------------------------------------------------------------- $select_queries
	/**
	 * @var string[]
	 */
	public array $select_queries = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name string|null
	 */
	public function __construct(string $name = null)
	{
		if (isset($name)) {
			$this->Name = $name;
		}
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName() : string
	{
		return $this->Name;
	}

}
