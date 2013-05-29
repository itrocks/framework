<?php
namespace SAF\Framework;
use mysqli_result;

/**
 * The mysql link for Dao
 */
class Mysql_Link extends Sql_Link
{

	//----------------------------------------------------------------------------------- $connection
	/**
	 * Connection to the mysqli server is a mysqli object
	 *
	 * @var Contextual_Mysqli
	 */
	private $connection;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct a new Mysql_Link using a parameters array, and connect to mysql database
	 *
	 * The $parameters array keys are : "host", "user", "password", "database".
	 *
	 * @param $parameters array
	 */
	public function __construct($parameters = null)
	{
		parent::__construct($parameters);
		$this->connect($parameters);
	}

	//----------------------------------------------------------------------------------------- begin
	public function begin()
	{
		$this->query("START TRANSACTION");
	}

	//---------------------------------------------------------------------------------------- commit
	public function commit()
	{
		$this->query("COMMIT");
	}

	//--------------------------------------------------------------------------------------- connect
	/**
	 * @param $parameters string[]
	 */
	private function connect($parameters)
	{
		if (!isset($parameters["database"]) && isset($parameters["databases"])) {
			$parameters["database"] = str_replace('*', '', $parameters["databases"]);
		}
		$this->connection = new Contextual_Mysqli(
			$parameters["host"], $parameters["user"],
			$parameters["password"], $parameters["database"]
		);
		$this->query("SET NAMES UTF8");
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * Delete an object from current data link
	 *
	 * If object was originally read from data source, corresponding data will be overwritten.
	 * If object was not originally read from data source, nothing is done and returns false.
	 *
	 * @param $object object object to delete from data source
	 * @return boolean true if deleted
	 * @see Data_Link::delete()
	 */
	public function delete($object)
	{
		$class_name = get_class($object);
		$id = $this->getObjectIdentifier($object);
		if ($id) {
			$class = Reflection_Class::getInstanceOf($class_name);
			foreach ($class->accessProperties() as $property) {
				if ($property->getAnnotation("link")->value == "Collection") {
					if ($property->getType()->isMultiple()) {
						$this->deleteCollection($object, $property, $property->getValue($object));
					}
					else {
						$this->delete($property->getValue($object));
					}
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
	 * This is called by delete() for linked object collection properties
	 *
	 * @param $parent object
	 * @param $property Reflection_Property
	 * @param $value mixed
	 */
	private function deleteCollection($parent, $property, $value)
	{
		$property_name = $property->name;
		$parent->$property_name = null;
		$old_collection = $parent->$property_name;
		$parent->$property_name = $value;
		if (isset($old_collection)) {
			foreach ($old_collection as $old_element) {
				$this->delete($old_element);
			}
		}
	}

	//---------------------------------------------------------------------------------- escapeString
	/**
	 * @param $string string
	 * @return string
	 */
	public function escapeString($string)
	{
		return $this->connection->escape_string($string);
	}

	//---------------------------------------------------------------------------------- executeQuery
	/**
	 * Execute an SQL query
	 *
	 * Sql_Link inherited classes must implement SQL query calls only into this method.
	 *
	 * @param $query string
	 * @return mysqli_result the sql query result set
	 */
	protected function executeQuery($query)
	{
		$limit = $this->limit();
		if (!empty($limit) && (substr($query, 0, 6) === "SELECT")) {
			$query .= " LIMIT 0, $limit";
		}
		return $this->connection->query($query);
	}

	//----------------------------------------------------------------------------------------- fetch
	/**
	 * Fetch a result from a result set to an object
	 *
	 * @param $result_set mysqli_result The result set : in most cases, will come from executeQuery()
	 * @param $class_name string The class name to store the result data into
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
	 * @param $result_set mysqli_result The result set : in most cases, will come from executeQuery()
	 * @return object
	 */
	protected function fetchRow($result_set)
	{
		$object = $result_set->fetch_row();
		return $object;
	}

	//------------------------------------------------------------------------------------------ free
	/**
	 * Free a result set
	 *
	 * Sql_Link inherited classes must implement freeing result sets only into this method.
	 *
	 * @param $result_set mysqli_result The result set : in most cases, will come from executeQuery()
	 */
	protected function free($result_set)
	{
		$result_set->free();
	}

	//--------------------------------------------------------------------------------- getColumnName
	/**
	 * Gets the column name from result set
	 *
	 * Sql_Link inherited classes must implement getting column name only into this method.
	 *
	 * @param $result_set mysqli_result The result set : in most cases, will come from executeQuery()
	 * @param $index integer|string The index of the column we want to get the SQL name from
	 * @return string
	 */
	protected function getColumnName($result_set, $index)
	{
		return $result_set->fetch_field_direct($index)->name;
	}

	//------------------------------------------------------------------------------- getColumnsCount
	/**
	 * Gets the column count from result set
	 *
	 * Sql_Link inherited classes must implement getting columns count only into this method.
	 *
	 * @param $result_set mysqli_result The result set : in most cases, will come from executeQuery()
	 * @return integer
	 */
	protected function getColumnsCount($result_set)
	{
		return $result_set->field_count;
	}

	//--------------------------------------------------------------------------- getStoredProperties
	/**
	 * Returns the list of properties of class $class that are stored into data link
	 *
	 * If data link stores properties not existing into $class, they are listed too,
	 * as if they where official properties of $class, but they storage object is a Dao_Column
	 * and not a Reflection_Property.
	 *
	 * @param $class string|Reflection_Class
	 * @return Reflection_Property[]|Mysql_Column[]
	 */
	public function getStoredProperties($class)
	{
		$properties = Reflection_Class::getInstanceOf($class)->getAllProperties();
		foreach ($properties as $key => $property) {
			$type = $property->getType();
			if ($property->isStatic() || $type->isMultiple()) {
				unset($properties[$key]);
			}
			elseif ($type->isClass()) {
				$properties["id_" . $property->name] = new Mysql_Column("id_" . $property->name);
			}
		}
		return $properties;
	}

	//----------------------------------------------------------------------------------------- query
	/**
	 * Executes an SQL query and returns the inserted record identifier (if applyable)
	 *
	 * @param $query string
	 * @return integer
	 */
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
	/**
	 * Read an object from data source
	 *
	 * @param $identifier integer identifier for the object
	 * @param $class      string class for read object
	 * @return object an object of class objectClass, read from data source, or null if nothing found
	 */
	public function read($identifier, $class)
	{
		if (!$identifier) return null;
		$this->setContext($class);
		$result_set = $this->executeQuery(
			"SELECT * FROM `" . $this->storeNameOf($class) . "` WHERE id = " . $identifier
		);
		$object = $result_set->fetch_object($class);
		$result_set->free();
		if ($object) {
			$this->setObjectIdentifier($object, $identifier);
		}
		return $object;
	}

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * Read all objects of a given class from data source
	 *
	 * @param $class string class for read objects
	 * @return object[] a collection of read objects
	 */
	public function readAll($class)
	{
		$read_result = array();
		$this->setContext($class);
		$result_set = $this->executeQuery("SELECT * FROM `" . $this->storeNameOf($class) . "`");
		while ($object = $result_set->fetch_object($class)) {
			$this->setObjectIdentifier($object, $object->id);
			$read_result[$object->id] = $object;
		}
		$result_set->free();
		return $read_result;
	}

	//-------------------------------------------------------------------------------------- rollback
	/**
	 * Rollback a transaction (non-transactional MySQL engines as MyISAM will do nothing and return null)
	 *
	 * @return boolean|null true if commit succeeds, false if error, null if not a transactional SQL engine
	 */
	public function rollback()
	{
		$this->query("ROLLBACK");
	}

	//---------------------------------------------------------------------------------------- search
	/**
	 * Search objects from data source
	 *
	 * It is highly recommended to instantiate the $what object using Search_Object::instantiate() in order to initialize all properties as unset and build a correct search object.
	 * If some properties are an not-loaded objects, the search will be done on the object identifier, without joins to the linked object.
	 * If some properties are loaded objects : if the object comes from a read, the search will be done on the object identifier, without join. If object is not linked to data-link, the search is done with the linked object as others search criterion.
	 *
	 * @param $what       object|array source object for filter, or filter array (need class_name) only set properties will be used for search
	 * @param $class_name string must be set if is not a filter array
	 * @return object[] a collection of read objects
	 */
	public function search($what, $class_name = null)
	{
		if (!isset($class_name)) {
			$class_name = get_class($what);
		}
		if (!(
			class_instanceof($class_name, 'SAF\Framework\Before_Search_Listener')
			&& !call_user_func(array($class_name, "beforeSearch"), $what)
		)) {
			$search_result = array();
			$builder = new Sql_Select_Builder($class_name, null, $what, $this);
			$query = $builder->buildQuery();
			$this->setContext($builder->getJoins()->getClassNames());
			$result_set = $this->executeQuery($query);
			if($result_set) {
				while ($object = $result_set->fetch_object($class_name)) {
					$this->setObjectIdentifier($object, $object->id);
					$search_result[$object->id] = $object;
				}
				$result_set->free();
			}
		}
		else {
			$search_result = array();
		}
		return $search_result;
	}

	//------------------------------------------------------------------------------------ setContext
	/**
	 * Set context for sql query
	 *
	 * @param $context_object string|string[] Can be a class name or an array of class names
	 */
	public function setContext($context_object)
	{
		$this->connection->context = $context_object;
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write an object into data source
	 *
	 * If object was originally read from data source, corresponding data will be overwritten
	 * If object was not originally read from data source nor linked to it using replace(), a new
	 * record will be written into data source using this object's data.
	 * If object is null (all properties null or unset), the object will be removed from data source
	 *
	 * @todo factorize this to become SOLID
	 * @param $object object object to write into data source
	 * @return object the written object
	 */
	public function write($object)
	{
		if (Null_Object::isNull($object)) {
			$this->removeObjectIdentifier($object);
		}
		elseif (!(
			class_implements(get_class($object), 'SAF\Framework\Before_Write_Listener')
			&& !$object->beforeWrite()
		)) {
			$class = Reflection_Class::getInstanceOf($object);
			$table_columns_names = array_keys($this->getStoredProperties($class));
			$write_collections = array();
			$write_maps = array();
			$write = array();
			$aop_getter_ignore = Aop_Getter::$ignore;
			Aop_Getter::$ignore = true;
			foreach ($class->accessProperties() as $property) if (!$property->isStatic()) {
				$value = isset($object->$property) ? $property->getValue($object) : null;
				if (is_null($value) && !$property->getAnnotation("null")->value) {
					$value = "";
				}
				if (in_array($property->name, $table_columns_names)) {
					// write basic
					if ($property->getType()->isBasic()) {
						$write[$property->name] = $value;
					}
					// write object id if set or object if no id is set (new object)
					else {
						$column_name = "id_" . $property->name;
						if (is_object($value) && (empty($object->$column_name))) {
							$object->$column_name = $this->getObjectIdentifier($value);
							if (empty($object->$column_name)) {
								$object->$column_name = $this->getObjectIdentifier($this->write($value));
							}
						}
						if (property_exists($object, $column_name)) {
							$write[$column_name] = intval($object->$column_name);
						}
					}
				}
				// write collection
				elseif ($property->getAnnotation("link")->value == "Collection") {
					$write_collections[] = array($property, $value);
				}
				// write map
				elseif (is_array($value)) {
					foreach ($value as $key => $val) {
						if (!is_object($val)) {
							$val = Dao::read($val, $property->getType()->getElementTypeAsString());
							if (isset($val)) {
								$value[$key] = $val;
							}
							else {
								unset($value[$key]);
							}
						}
					}
					$write_maps[] = array($property, $value);
				}
			}
			$class->accessPropertiesDone();
			Aop_Getter::$ignore = $aop_getter_ignore;
			$id = $this->getObjectIdentifier($object);
			$this->setContext($class->name);
			if (empty($id)) {
				$this->removeObjectIdentifier($object);
				$id = $this->query(Sql_Builder::buildInsert($class->name, $write));
				if (!empty($id)) {
					$this->setObjectIdentifier($object, $id);
				}
			}
			else {
				$this->query(Sql_Builder::buildUpdate($class->name, $write, $id));
			}
			foreach ($write_collections as $write) {
				list($property, $value) = $write;
				$this->writeCollection($object, $property, $value);
			}
			foreach ($write_maps as $write) {
				list($property, $value) = $write;
				$this->writeMap($object, $property, $value);
			}
			if (class_implements(get_class($object), 'SAF\Framework\After_Write_Listener')) {
				$object->afterWrite();
			}
		}
		return $object;
	}

	//------------------------------------------------------------------------------- writeCollection
	/**
	 * Write a component collection property value
	 *
	 * Ie when you write an order, it's implicitely needed to write it's lines
	 *
	 * @param $object     object
	 * @param $property   Reflection_Property
	 * @param $collection Component[]
	 */
	private function writeCollection($object, Reflection_Property $property, $collection)
	{
		// old collection
		$property_name = $property->name;
		$class_name = get_class($object);
		$old_object = Search_Object::create($class_name);
		$this->setObjectIdentifier($old_object, $this->getObjectIdentifier($object));
		$old_collection = isset($old_object->$property_name) ? $old_object->$property_name : array();
		// collection properties : write each of them
		$id_set = array();
		if ($collection) {
			foreach ($collection as $element) {
				$element->setComposite($object, $property->getAnnotation("foreign")->value);
				$id = $this->getObjectIdentifier($element);
				if (!empty($id)) {
					$id_set[$id] = true;
				}
				$this->write($element);
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
	 * @param $object   object
	 * @param $property Reflection_Property
	 * @param $map      object[]
	 */
	private function writeMap($object, Reflection_Property $property, $map)
	{
		// old map
		$property_name = $property->name;
		$class_name = get_class($object);
		$old_object = Search_Object::create($class_name);
		$this->setObjectIdentifier($old_object, $this->getObjectIdentifier($object));
		$old_map = isset($old_object->$property_name) ? $old_object->$property_name : array();
		// map properties : write each of them
		$insert_builder = new Sql_Map_Insert_Builder($property);
		$id_set = array();
		foreach ($map as $element) {
			$id = $this->getObjectIdentifier($element);
			if (!isset($old_map[$id])) {
				$query = $insert_builder->buildQuery($object, $element);
				$this->executeQuery($query);
			}
			$id_set[$id] = true;
		}
		// remove old unused elements
		$delete_builder = new Sql_Map_Delete_Builder($property);
		foreach ($old_map as $old_element) {
			$id = $this->getObjectIdentifier($old_element);
			if (!isset($id_set[$id])) {
				$query = $delete_builder->buildQuery($object, $old_element);
				$this->executeQuery($query);
			}
		}
	}

}
