<?php
namespace SAF\Framework;

trait Sql_Joins_Builder
{

	//---------------------------------------------------------------------------------------- $joins
	/**
	 * Joins used to build the SQL columns, where and having query parts
	 *
	 * @var Sql_Joins
	 */
	protected $joins;

	//------------------------------------------------------------------------------------ getClasses
	/**
	 * Gets the class names list used for each property path
	 *
	 * @return multitype:string
	 */
	public function getClasses()
	{
		return $this->joins->getClasses();
	}

	//--------------------------------------------------------------------------------- getClassNames
	/**
	 * Gets the class names list used for joins
	 *
	 * @return multitype:string
	 */
	public function getClassNames()
	{
		return $this->joins->getClassNames();
	}

	//------------------------------------------------------------------------------- getLinkedTables
	/**
	 * Gets the implicit linked tables used for joins
	 *
	 * @return multitype:multitype:string key is the table name, each has two field names
	 */
	public function getLinkedTables()
	{
		return $this->joins->getLinkedTables();
	}

}
