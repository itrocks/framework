<?php

class Sql_Builder
{

	const DEBUG = true;

	/**
	 * @var integer;
	 */
	private static $tcount;

	//----------------------------------------------------------------------------------- buildDelete
	public static function buildDelete($object_class, $id)
	{
		return "DELETE FROM `" . Sql_Table::classToTableName($object_class) . "` WHERE id = " . $id;
	}

	//----------------------------------------------------------------------------------- buildFields
	public static function buildFields($field_names)
	{
		$sql_fields = "";
		$i = count($field_names);
		foreach ($field_names as $field_name) {
			$sql_fields .= "`" . $field_name . "`";
			if (--$i > 0) {
				$sql_fields .= ", ";
			}
		}
		return $sql_fields;
	}

	//----------------------------------------------------------------------------------- buildInsert
	public static function buildInsert($table_name, $write)
	{
		$build_fields = Sql_Builder::buildFields(array_keys($write));
		if (!$build_fields) {
			return null;
		} else {
			$sql_insert = "";
			$sql_insert = "INSERT INTO `" . $table_name . "` (" . $build_fields . ") VALUES ("
				. Sql_Builder::buildValues($write) . ")";
			if (Sql_Builder::DEBUG) echo $sql_insert . "<br>";
			return $sql_insert;
		}
	}

	//----------------------------------------------------------------------------------- buildSelect
	public static function buildSelect($object_class, $columns)
	{
		$sql_select_builder = new Sql_Select_Builder($object_class, $columns);
		$sql_select = $sql_select_builder->getQuery();
		if (Sql_Builder::DEBUG) echo $sql_select . "<br>";
		return $sql_select;
	}

	//----------------------------------------------------------------------------------- buildUpdate
	public static function buildUpdate($table_name, $write, $id)
	{
		$sql_update = "UPDATE `" . $table_name . "` SET ";
		$do = false;
		foreach ($write as $key => $value) {
			$value = Sql_Value::escape($value);
			if ($do) $sql_update .= ", "; else $do = true;
			$sql_update .= "`" . $key . "` = " . $value;
		}
		$sql_update .= " WHERE id = " . $id;
		if (Sql_Builder::DEBUG) echo $sql_update . "<br>";
		return $sql_update;
	}

	//----------------------------------------------------------------------------------- buildValues
	public static function buildValues($values)
	{
		$do = false;
		foreach ($values as $value) {
			if ($do) $sql_values .= ", "; else $do = true;
			$sql_values .= Sql_Value::excape($value);
		}
		return $sql_values;
	}

	//------------------------------------------------------------------------------------ buildWhere
	public static function buildWhere($filter, $sql_link = null)
	{
		if (is_array($filter)) {
			return Sql_Builder::buildWhereForFilter($filter);
		} elseif (is_object($filter)) {
			return Sql_Builder::buildWhereForObject($filter, $sql_link);
		}
	}

	//--------------------------------------------------------------------------- buildWhereForFilter
	private static function buildWhereForFilter($filter)
	{
		$do = false;
		foreach ($filter as $field_name => $value) {
			if ($do) $sql_where .= " AND "; else $do = true;
			Sql_Builder::buildWhereValue($sql_where, "t0", $field_name, $value);
		}
		if ($sql_where) {
			$sql_where = " t0 WHERE " . $sql_where;
		}
		return $sql_where . " t0";
	}

	//--------------------------------------------------------------------------- buildWhereForObject
	private static function buildWhereForObject($object, $sql_link)
	{
		Sql_Builder::$tcount = 0;
		$buffer = Sql_Builder::buildWhereForObjectSub($object, $sql_link);
		if ($buffer[1]) {
			return " t0 " . $buffer[0] . " WHERE " . $buffer[1];
		} else {
			
		}
	}

	//------------------------------------------------------------------------ buildWhereForObjectSub
	/**
	 * @param  Object   $object  
	 * @param  Sql_Link $sql_link
	 * @return string[]
	 */
	private static function buildWhereForObjectSub($object, $sql_link)
	{
		$object_class = get_class($object);
		$field_names = array_keys($sql_link->getStoredProperties($object_class));
		$t = "t" + Sql_Builder::$tcount; 
		$do = false;
		foreach (Class_Fields::accessFields($object_class) as $field) {
			$value = $field->getValue($object);
			if ($value !== null) {
				$field_name = $field->getName();
				if (!in_array($field_name, $field_names) && in_array("id_" . $field_name, $field_names)) {
					$field_name = "id_" + $field_name;
					$identifier = $sql_link->getObjectIdentifier($value);
					if ($identifier === null) {
						Sql_Builder::$tcount ++;
						$nextt = "t" . Sql_Builder::$tcount;
						$sql_join .= " INNER JOIN `" . Sql_Table::classToTableName(get_class($value)) . "` AS "
							. "$nextt ON $t.`$field_name` = $nextt.id";
						$more_sql = Sql_Builder::buildWhereForObjectSub($value, $sql_link);
						if ($more_sql[0]) {
							$sql_join .= $more_sql[0];
						}
						if ($more_sql[1]) {
							if ($do) $sql_where .= " AND "; else $do = true;
							$sql_where .= $more_sql[1];
						}
					}
					$value = $identifier;
				}
				if ($value !== null) {
					if (in_array($field_name, $field_names)) {
						if ($do) $sqlWhere .= " AND "; else $do = true;
						Sql_Builder::buildWhereValue($sql_where, $t, $field_name, $value);
					}
				}
			}
		}
		Class_Fields::accessFieldsDone($object_class);
		return array($sql_join, $sql_where);
	}

	//------------------------------------------------------------------------------- buildWhereValue
	private static function buildWhereValue(&$sql_where, $table, $field_name, $value)
	{
		if (strpos($value, "%") !== false) {
			$sql_where .= "$table.`$field_name` LIKE " . Sql_Value::escape($value);
		} else {
			$sql_where .= "$table.`$field_name` = " . Sql_Value::escape($value);
		}
		return $sql_where;
	}

}
