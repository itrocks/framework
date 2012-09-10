<?php
namespace SAF\Framework;

/**
 * @todo Mysql_Link must be rewritten : call query(), executeQuery(), and standard protected methods instead of mysql_*
 * @todo some unitary tests to check all of this
 */
class Mysql_Link extends Sql_Link
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct a new Mysql_Link using a parameters array, and connect to mysql database
	 *
	 * The $parameters array keys are : "host", "user", "password", "database".
	 *
	 * @param multitype:string $parameters
	 */
	public function __construct($parameters)
	{
		$this->connection = mysql_connect(
			$parameters["host"], $parameters["user"], $parameters["password"]
		);
		if (isset($parameters["databases"]) && !isset($parameters["database"])) {
			$parameters["database"] = str_replace("*", "", $parameters["databases"]);
		}
		mysql_select_db($parameters["database"], $this->connection);
	}

	//---------------------------------------------------------------------------------------- delete
	public function delete($object)
	{
		$class_name = get_class($object);
		$id = $this->getObjectIdentifier($object);
		if ($id) {
			$class = Reflection_Class::getInstanceOf($class_name);
			foreach ($class->accessProperties() as $property) {
				if ($property->isContained()) {
					$this->deleteCollection($object, $property, $property->get($object));
				}
			}
			$class->accessPropertiesDone();
			$this->query(Sql_Builder::buildDelete($class_name, $id));
			$this->removeObjectIdentifier($object);
			return true;
		}
		return false;
	}

	//------------------------------------------------------------------------------ deleteCollection
	/**
	 * Delete a collection of object
	 *
	 * This is called by delete() for hard-linked object collection properties : only if the matching property has @contained.
	 *
	 * @param object              $parent
	 * @param Reflection_Property $property
	 * @param mixed               $value
	 */
	private function deleteCollection($parent, $property, $value)
	{
		$parent->$property = null;
		$getter = $property->getGetterName();
		$old_collection = $parent->$getter();
		$parent->$property = $value;
		foreach ($old_collection as $old_element) {
			$this->delete($old_element);
		}
	}

	//------------------------------------------------------------------------------------- deleteMap
	/**
	 * @todo implementation and use
	 * @param object $value
	 */
	private function deleteMap($value)
	{
	}

	//---------------------------------------------------------------------------------- executeQuery
	protected function executeQuery($query)
	{
		return mysql_query($query, $this->connection);
	}

	//----------------------------------------------------------------------------------------- fetch
	protected function fetch($result_set, $class_name = null)
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
	public function getStoredProperties($class)
	{
		if (is_string($class)) {
			$class = Reflection_Class::getInstanceOf($class);
		}
		$result_set = mysql_query(
			"SHOW COLUMNS FROM `" . Sql_Table::classToTableName($class->name) . "`",
			$this->connection
		);
		while ($column = mysql_fetch_object($result_set, __NAMESPACE__ . "\\Mysql_Column")) {
			$column_name = $column->getName();
			if (substr($column_name, 0, 3) == "id_") {
				$column_name = substr($column_name, 3);
			}
			$columns[$column_name] = $column;
		}
		mysql_free_result($result_set);
		$object_properties = $class->getAllProperties();
		return array_intersect_key($object_properties, $columns);
	}

	//----------------------------------------------------------------------------------------- query
	public function query($query)
	{
		if ($query) {
			$this->executeQuery($query);
			return @mysql_insert_id($this->connection);
		}
		else {
			return null;
		}
	}

	//------------------------------------------------------------------------------------------ read
	public function read($id, $class)
	{
		if (!$id) return null;
		$result_set = mysql_query(
			"SELECT * FROM `" . Sql_Table::classToTableName($class) . "` WHERE id = " . $id,
			$this->connection
		);
		$object = mysql_fetch_object($result_set, $class);
		mysql_free_result($result_set);
		$this->setObjectIdentifier($object, $id);
		return $object;
	}

	//--------------------------------------------------------------------------------------- readAll
	public function readAll($class)
	{
		$read_result = array();
		$result_set = mysql_query(
			"SELECT * FROM `" . Sql_Table::classToTableName(objectClass) . "`",
			$this->connection
		);
		while ($object = mysql_fetch_object($result_set, $class)) {
			$this->setOjectIdentifier($object, $object->id);
			$read_result[] = $object;
		}
		mysql_free_result($result_set);
		return $read_result;
	}

	//---------------------------------------------------------------------------------------- search
	public function search($what)
	{
		$class = get_class($what);
		$search_result = array();
		$builder = new Sql_Select_Builder(get_class($what), null, $what, $this);
		$result_set = mysql_query($builder->buildQuery(), $this->connection);
		while ($object = mysql_fetch_object($result_set, $class)) {
			$this->setObjectIdentifier($object, $object->id);
			$search_result[] = $object;
		}
		mysql_free_result($result_set);
		return $search_result;
	}

	//----------------------------------------------------------------------------------------- write
	public function write($object)
	{
		$class = Reflection_Class::getInstanceOf($object);
		$table_columns_names = array_keys($this->getStoredProperties($class));
		$id = $this->getObjectIdentifier($object);
		foreach ($class->accessProperties() as $property) {
			$value = $property->getValue($object);
			if (in_array($property->name, $table_columns_names)) {
				$write[$property->name] = $value;
			}
			elseif (in_array("id_" . $property->name, $table_columns_names)) {
				$this->writeIdColumn($write, $property->name, $value);
			}
			elseif ($property->isContained()) {
				$write_collections[$property->name] = $value;
			}
			elseif (is_array($value)) {
				$this->writeMap($value);
			}
		}
		if ($id === 0) {
			$this->removeObjectIdentifier($object);
			$id = null;
		}
		if ($id === null) {
			$id = $this->query(Sql_Builder::buildInsert($class, $write));
			if ($id != null) {
				$this->setObjectIdentifier($object, $id);
			}
		}
		else {
			$this->query(Sql_Builder::buildUpdate($class, $write, $id));
		}
		foreach ($write_collections as $property_name => $value) {
			$this->writeCollection($object, $property_name, $value);
		}
		$class->accessPropertiesDone();
		return $id;
	}

	//------------------------------------------------------------------------------- writeCollection
	/**
	 * Write a contained collection property value
	 *
	 * Ie when you write an order, it's implicitely needed to write it's lines
	 *
	 * @todo verify source and test it correctly
	 * @param object $parent
	 * @param string $property_name
	 * @param array  $collection
	 */
	private function writeCollection($parent, $property_name, $collection)
	{
		$property = Reflection_Property::getInstanceOf($parent, $property_name);
		$getter = $property->getGetterName();
		// old values
		$parent->$property_name = null;
		$old_collection = $parent->$getter();
		$parent->$property_name = $collection;
		// collection properties : write each of them
		$id_set = array();
		if ($property->isContained()) {
			foreach ($collection as $element) {
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
	private function writeIdColumn(&$write, $property_name, $value)
	{
		$int_value = is_numeric($value) ? $value : $this->getObjectIdentifier($value);
		if (!$int_value === null) {
			if ($value) {
				$this->write($value);
				$int_value = $this->getObjectIdentifier($value);
			}
			else {
				$int_value = 0;
			}
		}
		$write["id_" . $property_name] = $int_value;
		return $int_value;
	}

	//-------------------------------------------------------------------------------------- writeMap
	/**
	 * @todo not really implemented here
	 * @param unknown_type $map
	 */
	private function writeMap($map)
	{
		// map properties : write each of them
		foreach ($map as $element_key => $element_value) {
			$this->write($element_key);
			// TODO write with linked values ($element_key id must be written into $element_value property)
			$this->write($element_value);
		}
	}

}
