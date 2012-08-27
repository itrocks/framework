<?php

class Mysql_Link extends Sql_Link
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param string $database_path
	 */
	public function __construct($parameters)
	{
		$this->setConnection(
			mysql_connect($parameters["host"], $parameters["user"], $parameters["password"])
		);
		if ($parameters["databases"] && !$parameters["database"]) {
			$parameters["database"] = str_replace("*", "", $parameters["databases"]);
		}
		mysql_select_db($parameters["database"], $this->getConnection());
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * @param  Object  $object
	 * @return bool
	 */
	public function delete($object)
	{
		$object_class = get_class($object);
		$id = $this->getObjectIdentifier($object);
		if ($id) {
			foreach (Class_Fields::accessFields($object_class) as $class_field) {
				$value = $object->$class_field;
				if ($value instanceof Contained) {
					// TODO probably wrong : use the field type and not it's value that may be not initialized
					$this->deleteCollection($object, $class_field, $value);
				}
			}
			Class_Fields::accessFieldsDone($object_class);
			$this->query(Sql_Builder::buildDelete($object_class, $id));
			$this->removeObjectIdentifier($object);
			return true;
		}
		return false;
	}

	//------------------------------------------------------------------------------ deleteCollection
	/**
	 * @param Object              $parent
	 * @param Reflection_Property $field
	 * @param mixed               $value
	 */
	private function deleteCollection($parent, $field, $value)
	{
		$parent->$field = null;
		$getter = Getter::getGetter($field->getName());
		$old_collection = $parent->$getter();
		$parent->$field = $value;
		foreach ($old_collection as $old_element) {
			$this->delete($old_element);
		}
	}

	//------------------------------------------------------------------------------------- deleteMap
	/**
	 * @param Object $value
	 */
	private function deleteMap($value)
	{
		// TODO
	}

	//---------------------------------------------------------------------------------- executeQuery
	protected function executeQuery($query)
	{
		return mysql_query($query, $this->getConnection());
	}

	//----------------------------------------------------------------------------------------- fetch
	protected function fetch($result_set, $class_name)
	{
		return mysql_fetch_object($result_set, $class_name);
	}

	//------------------------------------------------------------------------------------------ free
	protected function free($result_set)
	{
		mysql_free_result($result_set);
	}

	//--------------------------------------------------------------------------------- getColumnName
	protected function getColumnName($result_set, $index)
	{
		return mysql_field_name($result_set, $index);
	}

	//------------------------------------------------------------------------------- getColumnsCount
	protected function getColumnsCount($result_set)
	{
		return mysql_num_fields($result_set);
	}

	//--------------------------------------------------------------------------- getStoredProperties
	public function getStoredProperties($object_class)
	{
		$result_set = mysql_query(
			"SHOW FIELDS FROM `" . Sql_Table::classToTableName($object_class) . "`",
			$this->getConnection()
		);
		while ($field = mysql_fetch_object($result_set, "Mysql_Field")) {
			$field_name = $field->getName();
			if (substr($field_name, 0, 3) == "id_") {
				$field_name = substr($field_name, 3);
			}
			$fields[$field_name] = $field;
		}
		mysql_free_result($result_set);
		$object_properties = Class_Fields::fields($object_class);
		return array_intersect_key($object_properties, $fields);
	}

	//----------------------------------------------------------------------------------------- query
	/**
	 * @param  string $query
	 * @return int
	 */
	public function query($query)
	{
		if ($query) {
			mysql_query($query, $this->getConnection());
			return @mysql_insert_id($this->getConnection());
		} else {
			return null;
		}
	}

	//------------------------------------------------------------------------------------------ read
	public function read($id, $object_class)
	{
		if (!$id) return null;
		if (Sql_Builder::DEBUG) {
			echo "SELECT * FROM `" . Sql_Table::classToTableName($object_class) . "` WHERE `id` = $id<br>";
		}
		$result_set = mysql_query(
			"SELECT * FROM `" . Sql_Table::classToTableName($object_class) . "` WHERE `id` = " . $id,
			$this->getConnection()
		);
		$object = mysql_fetch_object($result_set, $object_class);
		mysql_free_result($result_set);
		$this->setObjectIdentifier($object, $id);
		return $object;
	}

	//--------------------------------------------------------------------------------------- readAll
	public function readAll($object_class)
	{
		$read_result = array();
		if (Sql_Builder::DEBUG) {
			echo "SELECT * FROM `" . Sql_Table::classToTableName(objectClass) . "`<br>";
		}
		$result_set = mysql_query(
			"SELECT * FROM `" . Sql_Table::classToTableName(objectClass) . "`",
			$this->getConnection()
		);
		while ($object = mysql_fetch_object($result_set, $object_class)) {
			$this->setOjectIdentifier($object, $object->id);
			$read_result[] = $object;
		}
		mysql_free_result($result_set);
		return $read_result;
	}

	//--------------------------------------------------------------------------------------- replace
	public function replace($destination, $source)
	{
		$this->setObjectIdentifier($destination, $this->getObjectIdentifier($source));
		$this->write($destination);
		return $destination;
	}

	//---------------------------------------------------------------------------------------- search
	public function search($what)
	{
		$object_class = get_class($what);
		$search_result = array();
		$where = Sql_Builder::buildWhere($what, $this);
		if (Sql_Builder::DEBUG) {
			echo "SELECT t0.* FROM `" . SQL_Table::classToTableName($object_class) . "`" . $where . "<br>";
		}
		$result_set = mysql_query(
			"SELECT t0.* FROM `" . SQL_Table::classToTableName($object_class) . "`" . $where,
			$this->getConnection()
		);
		while ($object = mysql_fetch_object($result_set)) {
			$this->setObjectIdentifier($object, $object->id);
			$search_result[] = $object;
		}
		mysql_free_result($result_set);
		return $search_result;
	}

	//----------------------------------------------------------------------------------------- write
	public function write($object)
	{
		$object_class = get_class($object);
		$table_name = SQL_Table::classToTableName($object_class);
		$table_fields_names = array_keys(Mysql_Table::getFields($this, $object_class));
		$id = $this->getObjectIdentifier($object);
		foreach (Class_Fields::accessFields($object_class) as $class_field) {
			$value = $class_field->getValue($object);
			if (in_array($class_field->getName(), $table_fields_names)) {
				$write[$class_field->getName()] = $value;
			} elseif (in_array("id_" . $class_field->getName(), $table_fields_names)) {
				$this->writeIdColumn($write, $class_field->getName(), $value);
			} elseif ($value instanceof Contained) {
				// TODO check this "Contained" : is it right ?
				$write_collections[$class_field->getName()] = $value;
			} elseif (is_array($value)) {
				$this->writeMap($value);
			}
		}
		if ($id === 0) {
			$this->removeObjectIdentifier($object);
			$id = null;
		}
		if ($id === null) {
			$id = $this->query(Sql_Builder::buildInsert($table_name, $write));
			if ($id != null) {
				$this->setObjectIdentifier($object, $id);
			}
		} else {
			$this->query(Sql_Builder::buildUpdate($table_name, $write, $id));
		}
		foreach ($write_collections as $field_name => $value) {
			$this->writeCollection($object, $field_name, $value);
		}
		Class_Fields::accessFieldsDone($object_class);
		return $id;
	}

	//------------------------------------------------------------------------------- writeCollection
	private function writeCollection($parent, $field_name, $collection)
	{
		// old values
		$parent->$field_name = null;
		$getter = Getter::getGetter($field_name);
		$old_collection = $parent->$getter();
		$parent->$field_name = $collection;
		// collection fields : write each of them
		$id_set = array();
		foreach ($collection as $element) {
			if ($element instanceof Contained) {
				$id = $this->getObjectIdentifier($element);
				if ($id !== null) $id_set[] = $id;
				$this->write($element);
			}
		}
		// remove old unused elements
		foreach ($old_collection as $old_element) {
			$id = $this->getObjectIdentifier($old_element);
			if (!in_array($id, $id_set)) {
				$this->delete($old_element);
			}
		}
	}

	//--------------------------------------------------------------------------------- writeIdColumn
	private function writeIdColumn(&$write, $field_name, $value)
	{
		$int_value = is_numeric($value) ? $value : $this->getObjectIdentifier($value);
		if (!$int_value === null) {
			if ($value) {
				$this->write($value);
				$int_value = $this->getObjectIdentifier($value);
			} else {
				$int_value = 0;
			}
		}
		$write["id_" . $field_name] = $int_value;
		return $int_value;
	}

	//-------------------------------------------------------------------------------------- writeMap
	private function writeMap($map)
	{
		// map fields : write each of them
		foreach ($map as $element_key => $element_value) {
			$this->write($element_key);
			// TODO write with linked values (elementKey id must be written into elementValues field)
			$this->write($element_value);
		}
	}

}
