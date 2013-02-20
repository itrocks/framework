<?php
namespace SAF\Framework;

/**
 * Manages link tables for map properties
 */
class Sql_Link_Table
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	private $property;

	//---------------------------------------------------------------------------------------- $table
	/**
	 * @var string
	 */
	private $table;

	//-------------------------------------------------------------------------------- $master_column
	/**
	 * @var string
	 */
	private $master_column;

	//------------------------------------------------------------------------------- $foreign_column
	/**
	 * @var string
	 */
	private $foreign_column;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * $property @link annotation must be a Map to manage link tables
	 *
	 * @param Reflection_Property $property
	 */
	function __construct(Reflection_Property $property)
	{
		$this->property = $property;
	}

	//-------------------------------------------------------------------------------- foreign_column
	/**
	 * @return string
	 */
	function foreignColumn()
	{
		if (!isset($this->foreign_column)) {
			$this->foreign_column = "id_" . Names::setToClass(
				$this->property->getAnnotation("foreignlink")->value
			);
		}
		return $this->foreign_column;
	}

	//--------------------------------------------------------------------------------- master_column
	/**
	 * @return string
	 */
	function masterColumn()
	{
		if (!isset($this->master_column)) {
			$this->master_column = "id_" . Names::setToClass(
				$this->property->getAnnotation("foreign")->value
			);
		}
		return $this->master_column;
	}

	//----------------------------------------------------------------------------------------- table
	/**
	 * @return string
	 */
	function table()
	{
		if (!isset($this->table)) {
			$master_table  = Dao::storeNameOf($this->property->class);
			$foreign_table = Dao::storeNameOf($this->property->getType()->getElementTypeAsString());
			$this->table = ($master_table < $foreign_table)
				? $master_table . "_" . $foreign_table . "_links"
				: $foreign_table . "_" . $master_table . "_links";
		}
		return $this->table;
	}

}
