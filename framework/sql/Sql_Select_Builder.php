<?php

class Sql_Select_Builder
{

	/**
	 * @var string
	 */
	private $fields;

	/**
	 * @var Sql_Joins
	 */
	private $joins;

	/**
	 * @var string;
	 */
	private $tables;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct a SQL SELECT query
	 *
	 * Supported columns naming forms are :
	 * field_name : field_name must be a property of class
	 * field.foreign_field : field must be a property of class, foreign_field must be a property
	 *                       of field's @var class
	 *
	 * @param string    $object_class base object class name
	 * @param string[]  $columns      columns list
	 * @param Sql_Joins $sql_joins
	 */
	public function __construct($object_class, $columns, $sql_joins = null)
	{
		$this->table_counter = 0;
		$this->tables = "`" . Sql_Table::classToTableName($object_class) . "` t0";
		$this->joins = $sql_joins ? $sql_joins : new Sql_Joins($object_class, $columns);
		$this->buildFields($columns);
		$this->buildTables();
	}

	//----------------------------------------------------------------------------------- buildFields
	/**
	 * @param string[] $columns columns list
	 */
	private function buildFields($columns)
	{
		$first_column = true;
		foreach ($columns as $column) {
			if (!$first_column) {
				$this->fields .= ", ";
			}
			$first_column = false;
			$join = $this->joins->getJoin($column);
			if ($join) {
				// object property
				$first_property = true;
				foreach ($this->joins->getProperties($column) as $property) {
					$property_name = $property->getName();
					$property_type = $property->getType();
					$field_name = $property_name;
					$do_it = true;
					if (!Type::isBasic($property_type)) {
						$do_it = @class_exists($property_type);
						$field_name = "id_$field_name";
					}
					if ($do_it) {
						if (!$first_property) {
							$this->fields .= ", ";
						}
						$first_property = false;
						$this->fields .= "$join->foreign_alias.`$field_name` AS `$column:$property->name`";
					}
				}
			} else {
				$join = $this->joins->getJoin(lLastParse($column, "."));
				if ($join) {
					// joined property
					$field_name = rLastParse($column, ".");
					$this->fields .= "$join->foreign_alias.`$field_name` AS `$column`";
				} else {
					// single property
					$this->fields .= "t0.`$column` AS `$column`";
				}
			}
		}
	}

	//----------------------------------------------------------------------------------- buildTables
	private function buildTables()
	{
		foreach ($this->joins->getJoins() as $join) {
			$this->tables .= " $join->mode JOIN `$join->foreign_table` $join->foreign_alias"
			. " ON $join->foreign_alias.$join->foreign_field = $join->master_alias.$join->master_field";
		}
	}

	//-------------------------------------------------------------------------------------- getQuery
	/**
	 * @return string
	 */
	public function getQuery()
	{
		return "SELECT " . $this->fields . " FROM " . $this->tables;
	}

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * @return Sql_Joins
	 */
	public function getJoins()
	{
		return $this->sql_joins;
	}

}
