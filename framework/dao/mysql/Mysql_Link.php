<?php
namespace SAF\Framework;
use mysqli, mysqli_result;
use Serializable;

/**
 * @todo Mysql_Link must be rewritten : call query(), executeQuery(), and standard protected methods instead of mysql_*
 * @todo some unitary tests to check all of this
 */
class Mysql_Link extends Sql_Link
{

	//----------------------------------------------------------------------------------- $connection
	/**
	 * Connection to the mysqli server is a mysqli object
	 *
	 * @var mysqli
	 */
	private $connection;

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
		parent::__construct($parameters);
		$this->connect($parameters);
	}

	//--------------------------------------------------------------------------------------- connect
	private function connect($parameters)
	{
		if (!isset($parameters["database"]) && isset($parameters["databases"])) {
			$parameters["database"] = str_replace('*', '', $parameters["databases"]);
		}
		$this->connection = new mysqli(
			$parameters["host"], $parameters["user"],
			$parameters["password"], $parameters["database"]
		);
		$this->query("SET NAMES UTF8");
	}

	//---------------------------------------------------------------------------------------- delete
	public function delete($object)
	{
		$class_name = get_class($object);
		$id = $this->getObjectIdentifier($object);
		if ($id) {
			$class = Reflection_Class::getInstanceOf($class_name);
			foreach ($class->accessProperties() as $property) {
				if ($property->getAnnotation("contained")->value) {
					$this->deleteCollection($object, $property, $property->get($object));
				}
			}
			$class->accessPropertiesDone();
			$this->setContext($class_name);
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
		$getter = $property->getAnnotation("getter")->value;
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
	/**
	 * Execute an SQL query
	 *
	 * Sql_Link inherited classes must implement SQL query calls only into this method.
	 *
	 * @param string $query
	 * @return mysqli_result the sql query result set
	 */
	protected function executeQuery($query)
	{
		return $this->connection->query($query);
	}

	//----------------------------------------------------------------------------------------- fetch
	/**
	 * Fetch a result from a result set to an object
	 *
	 * @param mysqli_result $result_set The result set : in most cases, will come from executeQuery()
	 * @param string $class_name The class name to store the result data into
	 * @return object
	 */
	protected function fetch($result_set, $class_name = null)
	{
		$object = $result_set->fetch_object($class_name);
		return $object;
	}

	//-------------------------------------------------------------------------------------- fetchRow
	/**
	 * Fetch a result from a result set to an array
	 *
	 * @param mysqli_result $result_set The result set : in most cases, will come from executeQuery()
	 * @param string $class_name The class name to store the result data into
	 * @return object
	 */
	protected function fetchRow($result_set, $class_name = null)
	{
		$object = $result_set->fetch_row();
		return $object;
	}

	//------------------------------------------------------------------------------------------ free
	protected function free($result_set)
	{
		$result_set->free();
	}

	//--------------------------------------------------------------------------------- getColumnName
	protected function getColumnName($result_set, $index)
	{
		return $result_set->fetch_field_direct($index)->name;
	}

	//------------------------------------------------------------------------------- getColumnsCount
	protected function getColumnsCount($result_set)
	{
		return $result_set->field_count;
	}

	//--------------------------------------------------------------------------- getStoredProperties
	public function getStoredProperties($class)
	{
		if (is_string($class)) {
			$class = Reflection_Class::getInstanceOf($class);
		}
		$this->setContext($class->name);
		$result_set = $this->executeQuery(
			"SHOW COLUMNS FROM `" . $this->storeNameOf($class->name) . "`"
		);
		while ($column = $result_set->fetch_object(__NAMESPACE__ . "\\Mysql_Column")) {
			$column_name = $column->getName();
			if (substr($column_name, 0, 3) == "id_") {
				$column_name = substr($column_name, 3);
			}
			$columns[$column_name] = $column;
		}
		$result_set->free();
		$object_properties = $class->getAllProperties();
		return array_intersect_key($object_properties, $columns);
	}

	//----------------------------------------------------------------------------------------- query
	public function query($query)
	{
		if ($query) {
			$this->executeQuery($query);
			return $this->connection->insert_id;
		}
		else {
			return null;
		}
	}

	//------------------------------------------------------------------------------------------ read
	public function read($id, $class)
	{
		if (!$id) return null;
		$this->setContext($class);
		$result_set = $this->executeQuery(
			"SELECT * FROM `" . $this->storeNameOf($class) . "` WHERE id = " . $id
		);
		$object = $result_set->fetch_object($class);
		$result_set->free();
		if ($object) {
			$this->setObjectIdentifier($object, $id);
		}
		return $object;
	}

	//--------------------------------------------------------------------------------------- readAll
	public function readAll($class)
	{
		$read_result = array();
		$this->setContext($class);
		$result_set = $this->executeQuery("SELECT * FROM `" . $this->storeNameOf($class) . "`");
		while ($object = $result_set->fetch_object($class)) {
			$this->setObjectIdentifier($object, $object->id);
			$read_result[] = $object;
		}
		$result_set->free();
		return $read_result;
	}

	//---------------------------------------------------------------------------------------- search
	public function search($what, $class_name = null)
	{
		if (!isset($class_name)) {
			$class_name = get_class($what);
		}
		$search_result = array();
		$builder = new Sql_Select_Builder($class_name, null, $what, $this);
		$query = $builder->buildQuery();
		$this->setContext($builder->getClassNames());
		$result_set = $this->executeQuery($query);
		while ($object = $result_set->fetch_object($class_name)) {
			$this->setObjectIdentifier($object, $object->id);
			$search_result[] = $object;
		}
		$result_set->free();
		return $search_result;
	}

	//------------------------------------------------------------------------------------ setContext
	public function setContext($context_object)
	{
		$this->connection->context = $context_object;
	}

	//----------------------------------------------------------------------------------------- write
	public function write($object)
	{
		$class = Reflection_Class::getInstanceOf($object);
		$table_columns_names = array_keys($this->getStoredProperties($class));
		$write_collections = array();
		$write_maps = array();
		$aop_getter_ignore = Aop_Getter::$ignore;
		Aop_Getter::$ignore = true;
		foreach ($class->accessProperties() as $property) {
			$value = $property->getValue($object);
			if (is_null($value) && !$property->getAnnotation("null")->value) {
				$value = "";
			}
			if (in_array($property->name, $table_columns_names)) {
				if (Type::isBasic($property->getType())) {
					$write[$property->name] = $value;
				}
				else {
					$column_name = "id_" . $property->name;
					if (is_object($value) && (empty($object->$column_name))) {
						$object->$column_name = $this->getObjectIdentifier($value);
						if (empty($object->$column_name)) {
							$object->$column_name = $this->write($value);
						}
					}
					if (property_exists($object, $column_name)) {
						$write[$column_name] = $object->$column_name;
					}
				}
			}
			elseif ($property->getAnnotation("contained")->value) {
				$write_collections[$property->name] = $value;
			}
			elseif (is_array($value)) {
				$write_maps[$property->name] = $value;
			}
		}
		$class->accessPropertiesDone();
		Aop_Getter::$ignore = $aop_getter_ignore;
		$id = $this->getObjectIdentifier($object);
		if ($id === 0) {
			$this->removeObjectIdentifier($object);
			$id = null;
		}
		$this->setContext($class->name);
		if ($id === null) {
			$id = $this->query(Sql_Builder::buildInsert($class->name, $write));
			if ($id != null) {
				$this->setObjectIdentifier($object, $id);
			}
		}
		else {
			$this->query(Sql_Builder::buildUpdate($class->name, $write, $id));
		}
		foreach ($write_collections as $property_name => $value) {
			$this->writeCollection($object, $property_name, $value);
		}
		foreach ($write_maps as $property_name => $value) {
			$this->writeMap($value);
		}
		return $id;
	}

	//------------------------------------------------------------------------------- writeCollection
	/**
	 * Write a contained collection property value
	 *
	 * Ie when you write an order, it's implicitely needed to write it's lines
	 *
	 * @todo verify source and test it correctly
	 * @param object $object
	 * @param string $property_name
	 * @param array  $collection
	 */
	private function writeCollection($object, $property_name, $collection)
	{
		// old collection
		$old_object = Search_Object::newInstance(get_class($object));
		$this->setObjectIdentifier($old_object, $this->getObjectIdentifier($object));
		$old_collection = $old_object->$property_name;
		// collection properties : write each of them
		$id_set = array();
		$property = Reflection_Property::getInstanceOf(get_class($object), $property_name);
		if ($property->getAnnotation("contained")->value) {
			if ($collection && is_array(reset($collection))) {
				$class_name = Namespaces::fullClassName(Type::isMultiple($property->getType()));
				$collection = arrayToCollection($collection, $class_name);
			}
			foreach ($collection as $element) {
				if (!isset($representative_properties)) {
					$element_class = Reflection_Class::getInstanceOf(get_class($element));
					$representative_properties = $element_class->getAnnotation("representative")->value;
					$default = $element_class->getDefaultProperties();
				}
				$do_write = false;
				foreach ($representative_properties as $property_name) {
					$element_value = $element->$property_name;
					if (!empty($element_value) && ($element_value != strval($default[$property_name]))) {
						$do_write = true;
						break;
					}
				}
				if ($do_write) {
					if ($element instanceof Contained) {
						$element->setParent($object);
					}
					$id = $this->getObjectIdentifier($element);
					if (!empty($id)) {
						$id_set[$id] = true;
					}
					$this->write($element);
				}
			}
		}
		// remove old unused elements
		foreach ($old_collection as $old_element) {
			$id = $this->getObjectIdentifier($old_element);
			if (!isset($id_set[$id])) {
				$this->delete($old_element);
			}
		}
	}

	//-------------------------------------------------------------------------------------- writeMap
	/**
	 * @todo not really implemented here
	 * @param array $map
	 */
	private function writeMap($map)
	{
		// map properties : write each of them
// 		foreach ($map as $element_key => $element_value) {
// 			$this->write($element_key);
// 			// TODO write with linked values ($element_key id must be written into $element_value property)
// 			$this->write($element_value);
// 		}
	}

}
