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
	 * The $parameters array keys are : 'host', 'login', 'password', 'database'.
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
		$this->query('START TRANSACTION');
	}

	//---------------------------------------------------------------------------------------- commit
	public function commit()
	{
		$this->query('COMMIT');
	}

	//--------------------------------------------------------------------------------------- connect
	/**
	 * @param $parameters string[]
	 */
	private function connect($parameters)
	{
		if (!isset($parameters['database']) && isset($parameters['databases'])) {
			$parameters['database'] = str_replace('*', '', $parameters['databases']);
		}
		$this->connection = new Contextual_Mysqli(
			$parameters['host'], $parameters['login'],
			$parameters['password'], $parameters['database']
		);
		$this->query('SET NAMES UTF8');
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * Count the number of elements that match filter
	 *
	 * @param $what       object|array source object for filter, only set properties will be used
	 * @param $class_name string must be set if is $what is a filter array instead of a filter object
	 * @return integer
	 */
	public function count($what, $class_name = null)
	{
		$builder = new Sql_Count_Builder($class_name, $what, $this);
		$query = $builder->buildQuery();
		$this->setContext($builder->getJoins()->getClassNames());
		$result_set = $this->executeQuery($query);
		if ($result_set) {
			$row = $result_set->fetch_row();
			$result_set->free();
		}
		else {
			$row = [0 => 0];
		}
		return $row[0];
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
			$class = new Reflection_Class($class_name);
			$link = $class->getAnnotation('link')->value;
			$exclude_properties = $link
				? array_keys((new Reflection_Class($link))->getAllProperties())
				: [];
			foreach ($class->accessProperties() as $property) {
				if (!$property->isStatic() && !in_array($property->name, $exclude_properties)) {
					if ($property->getAnnotation('link')->value == 'Collection') {
						if ($property->getType()->isMultiple()) {
							$this->deleteCollection($object, $property, $property->getValue($object));
						}
						else {
							$this->delete($property->getValue($object));
						}
					}
				}
			}
			$this->setContext($class_name);
			$this->query(Sql_Builder::buildDelete($class_name, $id));
			$this->disconnect($object);
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
	 * Escape string into string or binary values
	 *
	 * @param $value string
	 * @return string
	 */
	public function escapeString($value)
	{
		if (is_object($value)) {
			$value = serialize($value);
		}
		return $this->connection->escape_string($value);
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
		return $result_set->fetch_object(Builder::className($class_name));
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

	//--------------------------------------------------------------------------------- getConnection
	/**
	 * Gets raw connection object
	 *
	 * @return Contextual_Mysqli
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	//---------------------------------------------------------------------------------- getRowsCount
	/**
	 * Gets the count of rows read / changed by the last query
	 *
	 * Sql_Link inherited classes must implement getting rows count only into this method
	 *
	 * @param $result_set mixed The result set : in most cases, will come from executeQuery()
	 * @param $clause     string The SQL query was starting with this clause
	 * @param $options    Dao_Option[] If set, will set the result into Dao_Count_Option::$count
	 * @return integer will return null if $options is set but contains no Dao_Count_Option
	 */
	protected function getRowsCount($result_set, $clause, $options = null)
	{
		if (isset($options)) {
			foreach ($options as $option) {
				if ($option instanceof Dao_Count_Option) {
					$option->count = $this->getRowsCount($result_set, 'SELECT');
					return $option->count;
				}
			}
			return null;
		}
		else {
			if ($clause == 'SELECT') {
				$result = $this->executeQuery('SELECT FOUND_ROWS()');
				$row = $result->fetch_row();
				$result->free();
				return $row[0];
			}
			return $this->connection->affected_rows;
		}
	}

	//--------------------------------------------------------------------------- getStoredProperties
	/**
	 * Returns the list of properties of class $class that are stored into data link
	 *
	 * If data link stores properties not existing into $class, they are listed too,
	 * as if they where official properties of $class, but they storage object is a Dao_Column
	 * and not a Reflection_Property.
	 *
	 * @param $class Reflection_Class
	 * @return Reflection_Property[]|Mysql_Column[]
	 */
	public function getStoredProperties($class)
	{
		$properties = $class->getAllProperties();
		foreach ($properties as $key => $property) {
			$type = $property->getType();
			if ($property->isStatic() || ($type->isMultiple() && !$type->getElementType()->isBasic())) {
				unset($properties[$key]);
			}
			elseif ($type->isClass()) {
				$properties['id_' . $property->name] = new Mysql_Column('id_' . $property->name);
			}
		}
		return $properties;
	}

	//----------------------------------------------------------------------------------------- query
	/**
	 * Executes an SQL query and returns the inserted record identifier or the mysqli result object
	 *
	 * @param $query string
	 * @return integer|mysqli_result
	 */
	public function query($query)
	{
		if ($query) {
			$result = $this->executeQuery($query);
			return (substr($query, 0, 6) == 'SELECT') ? $result : $this->connection->insert_id;
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
	 * @param $class_name string class for read object
	 * @return object an object of class objectClass, read from data source, or null if nothing found
	 */
	public function read($identifier, $class_name)
	{
		if (!$identifier) return null;
		if ((new Reflection_Class($class_name))->getAnnotation('link')->value) {
			$query = (new Sql_Select_Builder($class_name, null, ['id' => $identifier], $this))
				->buildQuery();
		}
		else {
			// it's for optimisation purpose only
			$query = 'SELECT * FROM `' . $this->storeNameOf($class_name) . '` WHERE id = ' . $identifier;
		}
		$this->setContext($class_name);
		$result_set = $this->executeQuery($query);
		$object = $this->fetch($result_set, $class_name);
		$this->free($result_set);
		if ($object) {
			$this->setObjectIdentifier($object, $identifier);
		}
		return $object;
	}

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * Read all objects of a given class from data source
	 *
	 * @param $class_name string class for read objects
	 * @param $options    Dao_Option|Dao_Option[] some options for advanced read
	 * @return object[] a collection of read objects
	 */
	public function readAll($class_name, $options = null)
	{
		$read_result = [];
		$this->setContext($class_name);
		$query = (new Sql_Select_Builder($class_name, null, null, null, $options))->buildQuery();
		$result_set = $this->executeQuery($query);
		if (isset($options)) {
			$this->getRowsCount($result_set, 'SELECT', $options);
		}
		$keys = explode('.', $this->getKeyPropertyName($options));
		$object_key = array_pop($keys);
		while ($object = $this->fetch($result_set, $class_name)) {
			$this->setObjectIdentifier($object, $object->id);
			$key_object = $object;
			foreach ($keys as $key) $key_object = $key_object->$key;
			$read_result[$key_object->$object_key] = $object;
		}
		$this->free($result_set);
		return $read_result;
	}

	//----------------------------------------------------------------------------- replaceReferences
	/**
	 * Replace all references to $replaced by references to $replacement into the database.
	 * Already loaded objects will not be changed.
	 *
	 * @param $replaced    object
	 * @param $replacement object
	 * @return boolean true if replacement has been done, false if something went wrong
	 */
	public function replaceReferences($replaced, $replacement)
	{
		$table_name = $this->storeNameOf(get_class($replaced));
		$replaced_id = $this->getObjectIdentifier($replaced);
		$replacement_id = $this->getObjectIdentifier($replacement);
		if ($replaced_id && $replacement_id && $table_name) {
			foreach (Mysql_Foreign_Key::buildReferences($this->connection, $table_name) as $foreign_key) {
				$foreign_table_name = lParse($foreign_key->getConstraint(), '.');
				$foreign_field_name = $foreign_key->getFields()[0];
				$query = 'UPDATE `' . $foreign_table_name . '`'
					. ' SET `' . $foreign_field_name . '` = ' . $replacement_id
					. ' WHERE `' . $foreign_field_name . '` = ' . $replaced_id;
				$this->query($query);
				if ($this->connection->last_errno) {
					$error = true;
				}
			}
			return isset($error) ? false : true;
		}
		return false;
	}

	//-------------------------------------------------------------------------------------- rollback
	/**
	 * Rollback a transaction (non-transactional MySQL engines as MyISAM will do nothing and return null)
	 *
	 * @return boolean|null true if commit succeeds, false if error, null if not a transactional SQL engine
	 */
	public function rollback()
	{
		$this->query('ROLLBACK');
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
	 * @param $class_name string must be set if $what is a filter array and not an object
	 * @param $options    Dao_Option|Dao_Option[] some options for advanced search
	 * @return object[] a collection of read objects
	 */
	public function search($what, $class_name = null, $options = null)
	{
		if (!isset($class_name)) {
			$class_name = get_class($what);
		}
		if (
			(is_a($class_name, Before_Search::class, true))
			? call_user_func([$class_name, 'beforeSearch'], $what) : true
		) {
			$search_result = [];
			$builder = new Sql_Select_Builder($class_name, null, $what, $this, $options);
			$query = $builder->buildQuery();
			$this->setContext($builder->getJoins()->getClassNames());
			$result_set = $this->executeQuery($query);
			if (isset($options)) {
				$this->getRowsCount($result_set, 'SELECT', $options);
			}
			$keys = explode('.', $this->getKeyPropertyName($options));
			$object_key = array_pop($keys);
			while ($object = $this->fetch($result_set, $class_name)) {
				$this->setObjectIdentifier($object, $object->id);
				$key_object = $object;
				foreach ($keys as $key) $key_object = $key_object->$key;
				$search_result[$key_object->$object_key] = $object;
			}
			$this->free($result_set);
		}
		else {
			$search_result = [];
		}
		return $search_result;
	}

	//------------------------------------------------------------------------------------ setContext
	/**
	 * Set context for sql query
	 *
	 * @param $context_object string|string[] Can be a class name or an array of class names
	 */
	public function setContext($context_object = null)
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
	 * @param $object  object object to write into data source
	 * @param $options Dao_Option[]|Dao_Option some options for advanced write
	 * @return object the written object
	 */
	public function write($object, $options = [])
	{
		if ($options && !is_array($options)) {
			$options = [$options];
		}
		if (($object instanceof Before_Write) ? $object->beforeWrite($options) : true) {
			if (Null_Object::isNull($object)) {
				$this->disconnect($object);
			}
			$class = new Reflection_Class(get_class($object));
			$table_columns_names = array_keys($this->getStoredProperties($class));
			$write_collections = [];
			$write_maps = [];
			$write = [];
			$aop_getter_ignore = Getter::$ignore;
			Getter::$ignore = true;
			$link = $class->getAnnotation('link')->value;
			$exclude_properties = $link
				? array_keys((new Reflection_Class($link))->getAllProperties())
				: [];
			foreach ($options as $option) {
				if ($option instanceof Dao_Only_Option) {
					$only = array_merge(isset($only) ? $only : [], $option->properties);
				}
			}
			foreach ($class->accessProperties() as $property) {
				if (!isset($only) || in_array($property->name, $only)) {
					if (!$property->isStatic() && !in_array($property->name, $exclude_properties)) {
						$value = isset($object->$property) ? $property->getValue($object) : null;
						$property_is_null = $property->getAnnotation('null')->value;
						if (is_null($value) && !$property_is_null) {
							$value = '';
						}
						if (in_array($property->name, $table_columns_names)) {
							// write basic
							if ($property->getType()->getElementType()->isBasic()) {
								$write[$property->name] = $value;
							}
							// write object id if set or object if no id is set (new object)
							else {
								$column_name = 'id_' . $property->name;
								if (is_object($value)) {
									$object->$column_name = $this->getObjectIdentifier($value);
									if (empty($object->$column_name)) {
										$object->$column_name = $this->getObjectIdentifier($this->write($value));
									}
								}
								if (property_exists($object, $column_name)) {
									$write[$column_name] = ($property_is_null && !isset($object->$column_name))
										? null
										: intval($object->$column_name);
								}
							}
						}
						// write collection
						elseif (is_array($value) && ($property->getAnnotation('link')->value == 'Collection')) {
							$write_collections[] = [$property, $value];
						}
						// write map
						elseif (is_array($value) && ($property->getAnnotation('link')->value == 'Map')) {
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
							$write_maps[] = [$property, $value];
						}
					}
				}
			}
			Getter::$ignore = $aop_getter_ignore;
			$id = $this->getObjectIdentifier($object);
			$this->setContext($class->name);
			if (empty($id)) {
				$this->disconnect($object);
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
			if ($object instanceof After_Write) {
				$object->afterWrite($options);
			}
		}
		return $object;
	}

	//------------------------------------------------------------------------------- writeCollection
	/**
	 * Write a component collection property value
	 *
	 * Ie when you write an order, it's implicitly needed to write it's lines
	 *
	 * @param $object     object
	 * @param $property   Reflection_Property
	 * @param $collection Component[]
	 */
	private function writeCollection($object, Reflection_Property $property, $collection)
	{
		// old collection
		$class_name = get_class($object);
		$old_object = Search_Object::create($class_name);
		$this->setObjectIdentifier($old_object, $this->getObjectIdentifier($object));
		$aop_getter_ignore = Getter::$ignore;
		Getter::$ignore = false;
		$old_collection = $property->getValue($old_object);
		Getter::$ignore = $aop_getter_ignore;
		// collection properties : write each of them
		$id_set = [];
		if ($collection) {
			foreach ($collection as $element) {
				$element->setComposite($object, $property->getAnnotation('foreign')->value);
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
		$class_name = get_class($object);
		$old_object = Search_Object::create($class_name);
		$this->setObjectIdentifier($old_object, $this->getObjectIdentifier($object));
		$aop_getter_ignore = Getter::$ignore;
		Getter::$ignore = false;
		$old_map = $property->getValue($old_object);
		Getter::$ignore = $aop_getter_ignore;
		// map properties : write each of them
		$insert_builder = new Sql_Map_Insert_Builder($property);
		$id_set = [];
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
