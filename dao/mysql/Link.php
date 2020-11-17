<?php
namespace ITRocks\Framework\Dao\Mysql;

use Exception;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Dao\Option\Cache_Result;
use ITRocks\Framework\Dao\Option\Create_If_No_Result;
use ITRocks\Framework\Mapper\Abstract_Class;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Sql;
use ITRocks\Framework\Sql\Builder\Count;
use ITRocks\Framework\Sql\Builder\Select;
use ITRocks\Framework\Tools\Call_Stack;
use ITRocks\Framework\Tools\Contextual_Mysqli;
use mysqli_result;

/**
 * The mysql link for Dao
 */
class Link extends Dao\Sql\Link
{

	//------------------------------------------------------------------------------------- COLLATION
	const COLLATION = 'collation';

	//------------------------------------------------------------------------------------- GZINFLATE
	/**
	 * Actions for $prepared_fetch
	 */
	const GZINFLATE = 'gzinflate';

	//---------------------------------------------------------------------------------------- LATIN1
	/**
	 * LATIN1 collation value
	 */
	const LATIN1 = 'LATIN1';

	//------------------------------------------------------------------------------------------ UTF8
	/**
	 * UTF8 collation value
	 */
	const UTF8 = 'UTF8';

	//------------------------------------------------------------------------------------ $collation
	/**
	 * @var string
	 */
	private $collation = self::UTF8;

	//--------------------------------------------------------------------------------- $commit_stack
	/**
	 * @var integer
	 */
	private $commit_stack = 0;

	//--------------------------------------------------------------------------- $commit_stack_trace
	/**
	 * @var Call_Stack[]
	 */
	private $commit_stack_trace;

	//----------------------------------------------------------------------------------- $connection
	/**
	 * Connection to the mysqli server is a mysqli object
	 *
	 * @var Contextual_Mysqli
	 */
	private $connection;

	//---------------------------------------------------------------------------------------- $locks
	/**
	 * @var Lock[]
	 */
	private $locks = [];

	//------------------------------------------------------------------------------- $prepared_fetch
	/**
	 * @example ['content' => [self::GZINFLATE]]
	 * @var array key is the name of the property, value is an array of actions (eg GZINFLATE)
	 */
	private $prepared_fetch;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct a new Mysql using a parameters array, and connect to mysql database
	 *
	 * The $parameters array keys are : 'host', 'login', 'password', 'database'.
	 *
	 * @param $parameters array
	 */
	public function __construct(array $parameters = null)
	{
		parent::__construct($parameters);
		if (isset($parameters[self::COLLATION])) {
			$this->collation = $parameters[self::COLLATION];
		}
		if ($parameters) {
			$this->connect($parameters);
		}
	}

	//------------------------------------------------------------------------------------ __destruct
	public function __destruct()
	{
		if ($this->commit_stack) {
			trigger_error("Commit stack not closed", E_USER_WARNING);
			if (isset($GLOBALS['D'])) {
				$counter = 0;
				foreach ($this->commit_stack_trace as $call_stack) {
					echo 'Commit #' . (++$counter) . BRLF;
					echo $call_stack->asHtml();
				}
			}
		}
	}

	//----------------------------------------------------------------------------------------- begin
	/**
	 * Begin transaction
	 *
	 * If a transaction has already been begun, it is counted as we will need the same commits count
	 */
	public function begin()
	{
		if (!$this->commit_stack) {
			parent::begin();
			$this->query('START TRANSACTION');
		}
		$this->commit_stack ++;
		if (isset($GLOBALS['D'])) {
			echo "BEGIN #$this->commit_stack" . BRLF;
			echo PRE . (new Call_Stack)->asHtml() . _PRE;
			$this->commit_stack_trace[$this->commit_stack] = new Call_Stack();
		}
	}

	//---------------------------------------------------------------------------------------- commit
	/**
	 * @param $flush boolean if true, then all the pending transactions will be unstacked
	 * @return boolean
	 */
	public function commit($flush = false)
	{
		if ($flush) {
			$this->commit_stack = 0;
			if (isset($GLOBALS['D'])) {
				$this->commit_stack_trace = [];
			}
			$this->query('COMMIT');
		}
		elseif ($this->commit_stack > 0) {
			if (isset($GLOBALS['D'])) {
				echo "COMMIT #$this->commit_stack" . BRLF;
				echo PRE . (new Call_Stack)->asHtml() . _PRE;
			}
			$this->commit_stack --;
			if (!$this->commit_stack) {
				$this->query('COMMIT');
				if (isset($GLOBALS['D'])) {
					$this->commit_stack_trace = [];
				}
			}
		}
		if (!$this->commit_stack) {
			// call parent commit for events resolution, but do not take care of its constant return value
			parent::commit($flush);
		}
		return true;
	}

	//--------------------------------------------------------------------------------------- connect
	/**
	 * @param $parameters string[] ['host', 'login', 'password', 'database']
	 */
	private function connect(array $parameters)
	{
		if (!isset($parameters[self::DATABASE]) && isset($parameters['databases'])) {
			$parameters[self::DATABASE] = str_replace('*', '', $parameters['databases']);
		}
		$this->connection = new Contextual_Mysqli(
			$parameters[self::HOST],
			$parameters[self::LOGIN],
			$parameters[self::PASSWORD],
			$parameters[self::DATABASE] ?? null,
			$parameters[self::PORT] ?? 3306,
			$parameters[self::SOCKET] ?? null
		);
		$this->query('SET NAMES ' . $this->collation);
	}

	//------------------------------------------------------------------------------------- construct
	/**
	 * Alternative constructor that enables configuration insurance
	 *
	 * @param $host     string
	 * @param $login    string
	 * @param $password string
	 * @param $database string
	 * @return Link
	 */
	public static function construct($host, $login, $password, $database)
	{
		return new Link([
			self::DATABASE => $database,
			self::HOST     => $host,
			self::LOGIN    => $login,
			self::PASSWORD => $password
		]);
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * Count the number of elements that match filter
	 *
	 * @param $what       object|string|array source object, class name or properties for filter
	 * @param $class_name string must be set if is $what is a filter array instead of a filter object
	 * @param $options    Option|Option[] array some options for advanced search
	 * @return integer
	 */
	public function count($what, $class_name = null, $options = [])
	{
		if (is_string($what)) {
			$class_name = $what;
			$what       = null;
		}
		$class_name = Builder::className($class_name);
		$builder    = new Count($class_name, $what, $this, $options);
		$query      = $builder->buildQuery();
		array_push($this->connection->contexts, $builder->getJoins()->getClassNames());
		$result_set = $this->connection->query($query);
		if ($result_set) {
			$row = $result_set->fetch_row();
			$result_set->free();
		}
		else {
			$row = [0];
		}
		array_pop($this->connection->contexts);
		return $row[0];
	}

	//-------------------------------------------------------------------------- createObjectIfOption
	/**
	 * Instantiates a $class_name / get_class($what) object if a Create_If_No_Result option is set
	 *
	 * - $what values will be used to initialize the new object properties
	 * - $what Func values are ignored
	 * - $what array values for non-multiple properties are ignored
	 * - if $what contain 'or' searches (numeric keys), they are ignored
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $what       object|array source object for filter, or filter array (need class_name)
	 *                    only set properties will be used for search
	 * @param $class_name string must be set if $what is a filter array and not an object
	 * @param $options    Option[] some options, one can be Create_If_No_Result
	 * @return object|null
	 */
	protected function createObjectIfOption($what, $class_name, array $options)
	{
		foreach ($options as $option) {
			if ($option instanceof Create_If_No_Result) {
				if (!$class_name) {
					$class_name = get_class($what);
				}
				/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
				$object = Builder::create($class_name);
				$values = is_array($what) ? $what : get_object_vars($what);
				foreach ($values as $property_name => $value) {
					/** @noinspection PhpUnhandledExceptionInspection class name and property must be valid */
					if (!(
						is_numeric($property_name)
						|| ($value instanceof Func)
						|| (
							is_array($value)
							&& !(new Reflection_Property($class_name, $property_name))->getType()->isMultiple()
						)
					)) {
						$object->$property_name = $value;
					}
				}
				return $object;
			}
		}
		return null;
	}

	//--------------------------------------------------------------------------------- createStorage
	/**
	 * Create a storage space for $class_name objects
	 *
	 * If the storage space already exists, it is updated without losing data
	 *
	 * @param $class_name string
	 * @return boolean true if storage was created or updated, false if it was already up to date
	 */
	public function createStorage($class_name)
	{
		$class_name = Builder::className($class_name);
		return (new Maintainer())->updateTable($class_name, $this->connection);
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * Delete an object from current data link
	 *
	 * If object was originally read from data source, matching data will be overwritten.
	 * If object was not originally read from data source, nothing is done and returns false.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object object to delete from data source
	 * @return boolean true if deleted
	 * @see Data_Link::delete()
	 */
	public function delete($object)
	{
		/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
		$will_delete = Method_Annotation::callAll(
			(new Reflection_Class($object))->getAnnotations('before_delete'), $object, [$this]
		);

		if ($will_delete) {
			$id = $this->getObjectIdentifier($object);
			if ($id) {
				$class_name = get_class($object);
				/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
				$class = new Reflection_Class($class_name);
				$link  = Class_\Link_Annotation::of($class);
				/** @noinspection PhpUnhandledExceptionInspection link annotation value must be valid */
				$exclude_properties = $link->value
					? array_keys((new Reflection_Class($link->value))->getProperties([T_EXTENDS, T_USE]))
					: [];
				$this->begin();
				foreach ($class->accessProperties() as $property) {
					if (!$property->isStatic() && !in_array($property->name, $exclude_properties)) {
						if (Link_Annotation::of($property)->isCollection()) {
							if ($property->getType()->isMultiple()) {
								/** @noinspection PhpUnhandledExceptionInspection property from object accessible */
								$this->deleteCollection($object, $property, $property->getValue($object));
							}
							// TODO dead code ? @link Collection is only for @var object[], not for @var object
							else {
								/** @noinspection PhpUnhandledExceptionInspection property from object accessible */
								$this->delete($property->getValue($object));
								trigger_error(
									"Dead code into Mysql\\Link::delete() on {$property->name} is not so dead",
									E_USER_NOTICE
								);
							}
						}
					}
				}
				array_push($this->connection->contexts, $class_name);
				if ($link->value) {
					$id = [];
					foreach ($link->getLinkClass()->getUniqueProperties() as $link_property) {
						$property_name = $link_property->getName();
						if (Dao::storedAsForeign($link_property)) {
							$column_name      = 'id_' . Store_Name_Annotation::of($link_property)->value;
							$id[$column_name] = $this->getObjectIdentifier($object, $property_name);
						}
						else {
							$column_name = Store_Name_Annotation::of($link_property)->value;
							/** @noinspection PhpUnhandledExceptionInspection property from object accessible */
							$id[$column_name] = $link_property->getValue($object);
						}
					}
				}
				// delete query may crash if the database engine breaks it
				try {
					$this->query(Sql\Builder::buildDelete($class_name, $id));
					$this->disconnect($object);
					$this->commit();
				}
				catch (Exception $exception) {
					$this->rollback();
					/** @noinspection PhpUnhandledExceptionInspection but it might be... TODO LOW redeclare */
					throw $exception;
				}
				array_pop($this->connection->contexts);
				/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
				Method_Annotation::callAll(
					(new Reflection_Class($object))->getAnnotations('after_delete'), $object, [$this]
				);
				return true;
			}
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
		$property_name          = $property->name;
		$parent->$property_name = null;
		$old_collection         = $parent->$property_name;
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $value string|object
	 * @return string
	 */
	public function escapeString($value)
	{
		if (is_object($value)) {
			$id = $this->getObjectIdentifier($value, 'id');
			/** @noinspection PhpUnhandledExceptionInspection is_object */
			$properties = (new Reflection_Class($value))->getAnnotedProperties(
				Store_Annotation::ANNOTATION, Store_Annotation::FALSE
			);
			if ($properties) {
				$value = clone $value;
				foreach (array_keys($properties) as $property_name) {
					unset($value->$property_name);
				}
			}
			$value = is_numeric($id) ? $id : serialize($value);
		}
		return $this->connection->escape_string($value);
	}

	//----------------------------------------------------------------------------------------- fetch
	/**
	 * Fetch a result from a result set to an object
	 *
	 * @param $result_set mysqli_result The result set : in most cases, will come from query()
	 * @param $class_name string The class name to store the result data into
	 * @return object
	 */
	public function fetch($result_set, $class_name = null)
	{
		if ($object = $result_set->fetch_object(Builder::className($class_name))) {
			if ($object instanceof Abstract_Class) {
				$this->prepareFetch($object->class);
				$object = $this->read($this->getObjectIdentifier($object), $object->class);
			}
			// execute actions stored into $prepared_fetch
			foreach ($this->prepared_fetch as $property_name => $actions) {
				foreach ($actions as $action) {
					if ($action === self::GZINFLATE) {
						/** @noinspection PhpUsageOfSilenceOperatorInspection if not deflated */
						$value = @gzinflate($object->$property_name);
						if ($value !== false) {
							$object->$property_name = $value;
						}
					}
					elseif ($action instanceof Data_Link) {
						$value = $action->readProperty($object, $property_name);
						if (isset($value)) {
							$object->$property_name = $value;
						}
					}
				}
			}
		}
		return $object;
	}

	//-------------------------------------------------------------------------------------- fetchAll
	/**
	 * @param $class_name string
	 * @param $options    Option[]
	 * @param $result_set mysqli_result
	 * @return object[]
	 */
	protected function fetchAll($class_name, array $options, mysqli_result $result_set)
	{
		$search_result    = [];
		$keys             = $this->getKeyPropertyName($class_name, $options);
		$keys_is_callable = false;
		if (($keys !== 'id') && isset($keys) && !is_callable($keys)) {
			if (is_array($keys) && !($keys_is_callable = arrayIsCallable($keys))) {
				$object_key = [];
				foreach ($keys as $key => $value) {
					$keys[$key]       = explode(DOT, $value);
					$object_key[$key] = array_pop($keys[$key]);
				}
			}
			else {
				$keys       = explode(DOT, $keys);
				$object_key = array_pop($keys);
			}
		}
		$fetch_class_name = ((new Type($class_name))->isAbstractClass())
			? Abstract_Class::class
			: $class_name;
		$real_class_names = [];
		$this->prepareFetch($fetch_class_name);
		while ($object = $this->fetch($result_set, $fetch_class_name)) {
			$this->setObjectIdentifier($object, $object->id);
			// the most common key is the record id : do it quick
			if ($keys === 'id') {
				$id = $object->id;
				if ($fetch_class_name === Abstract_Class::class) {
					$real_class_name = get_class($object);
					if (!isset($real_class_names[$real_class_name])) {
						$real_class_names[$real_class_name] = Builder::className($real_class_name);
					}
					$id = $real_class_names[$real_class_name] . ':' . $id;
				}
				$search_result[$id] = $object;
			}
			// result key can be calculated from a callable (call-back function)
			elseif ($keys_is_callable || is_callable($keys)) {
				$search_result[call_user_func($keys, $object)] = $object;
			}
			// complex keys
			elseif (isset($keys) && isset($object_key)) {
				// result key must be a set of several id keys (used for linked classes collections)
				// (Dao::key(['property_1', 'property_2']))
				if (is_array($object_key)) {
					$k_key    = '';
					$multiple = count($keys) > 1;
					foreach ($keys as $key => $k_keys) {
						$k_object_key = $object_key[$key];
						$key_object   = $object;
						foreach ($k_keys as $key) {
							$key_object = $key_object->$key;
						}
						$k_id_object_key = 'id_' . $k_object_key;
						$k_key          .= ($k_key ? Link_Class::ID_SEPARATOR : '')
							. ($multiple ? ($k_object_key . '=') : '')
							. (
								isset($key_object->$k_id_object_key)
								? $key_object->$k_id_object_key
								: $key_object->$k_object_key
							);
					}
					$search_result[$k_key] = $object;
				}
				// result key must be a single id key (Dao::key('property_name'))
				else {
					$key_object = $object;
					foreach ($keys as $key) {
						$key_object = $key_object->$key;
					}
					$search_result[$key_object->$object_key] = $object;
				}
			}
			// we don't want a significant result key that (Dao::key(null))
			else {
				$search_result[] = $object;
			}
		}
		$this->free($result_set);
		return $search_result;
	}

	//-------------------------------------------------------------------------------------- fetchRow
	/**
	 * Fetch a result from a result set to an array
	 *
	 * @param $result_set mysqli_result The result set : in most cases, will come from query()
	 * @return object
	 */
	public function fetchRow($result_set)
	{
		return $result_set->fetch_row();
	}

	//------------------------------------------------------------------------------------------ free
	/**
	 * Free a result set
	 *
	 * Sql_Link inherited classes must implement freeing result sets only into this method.
	 *
	 * @param $result_set mysqli_result The result set : in most cases, will come from query()
	 */
	public function free($result_set)
	{
		$result_set->free();
	}

	//--------------------------------------------------------------------------------- getColumnName
	/**
	 * Gets the column name from result set
	 *
	 * Sql_Link inherited classes must implement getting column name only into this method.
	 *
	 * @param $result_set mysqli_result The result set : in most cases, will come from query()
	 * @param $index integer|string The index of the column we want to get the SQL name from
	 * @return string
	 */
	public function getColumnName($result_set, $index)
	{
		return $result_set->fetch_field_direct($index)->name;
	}

	//------------------------------------------------------------------------------- getColumnsCount
	/**
	 * Gets the column count from result set
	 *
	 * Sql_Link inherited classes must implement getting columns count only into this method.
	 *
	 * @param $result_set mysqli_result The result set : in most cases, will come from query()
	 * @return integer
	 */
	public function getColumnsCount($result_set)
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

	//----------------------------------------------------------------------- getLinkObjectIdentifier
	/**
	 * Link classes objects identifiers are the identifiers of all their composite properties values
	 * also known as link properties values.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @param $link   Class_\Link_Annotation send it for optimization, but this is not mandatory
	 * @return string identifiers in a single string, separated with '.'
	 */
	public function getLinkObjectIdentifier($object, Class_\Link_Annotation $link = null)
	{
		if (!$object) {
			return null;
		}
		if (!isset($link)) {
			/** @noinspection PhpUnhandledExceptionInspection object */
			$link = Class_\Link_Annotation::of(new Reflection_Class($object));
		}
		if ($link->value) {
			$ids        = [];
			$link_class = $link->getLinkClass();
			foreach ($link_class->getUniqueProperties() as $link_property) {
				$property_name = $link_property->getName();
				if (Dao::storedAsForeign($link_property)) {
					$id = parent::getObjectIdentifier($object, $property_name);
					if (!isset($id)) {
						$link_property = $link_class->getCompositeProperty(null, false);
						if ($link_property && ($link_property->name === $property_name)) {
							$id = isset($object->id) ? $object->id : null;
							if (!isset($id)) {
								return null;
							}
						}
						else {
							return null;
						}
					}
				}
				else {
					/** @noinspection PhpUnhandledExceptionInspection valid $link_property from $object */
					$id = $link_property->getValue($object);
				}
				$ids[] = $property_name . '=' . $id;
			}
			sort($ids);
			return join(Link_Class::ID_SEPARATOR, $ids);
		}
		return null;
	}

	//--------------------------------------------------------------------------- getObjectIdentifier
	/**
	 * Used to get an object's identifier
	 *
	 * A null value will be returned for an object that is not linked to data link.
	 * If $object is already an identifier, the identifier is returned.
	 *
	 * @param $object        object an object to get data link identifier from
	 * @param $property_name string a property name to get data link identifier from instead of object
	 * @return mixed you can test if an object identifier is set with empty($of_this_result)
	 */
	public function getObjectIdentifier($object, $property_name = null)
	{
		return (is_object($object) && isset($property_name))
			? parent::getObjectIdentifier($object, $property_name)
			: ($this->getLinkObjectIdentifier($object) ?: parent::getObjectIdentifier($object));
	}

	//---------------------------------------------------------------------------------- getRowsCount
	/**
	 * Gets the count of rows read / changed by the last query
	 *
	 * Sql_Link inherited classes must implement getting rows count only into this method
	 *
	 * @param $clause     string The SQL query was starting with this clause
	 * @param $options    Option|Option[] If set, will set the result into Dao_Count_Option::$count
	 * @param $result_set mixed The result set : in most cases, will come from query()
	 * @return integer will return null if $options is set but contains no Dao_Count_Option
	 */
	public function getRowsCount($clause, $options = [], $result_set = null)
	{
		if ($options && $result_set) {
			if (!is_array($options)) {
				$options = $options ? [$options] : [];
			}
			foreach ($options as $option) {
				if ($option instanceof Option\Count) {
					$option->count = $this->getRowsCount($clause);
					return $option->count;
				}
			}
			return null;
		}
		elseif ($clause === 'SELECT') {
			$result = $this->connection->query('SELECT FOUND_ROWS()');
			$row    = $result->fetch_row();
			$result->free();
			return $row[0];
		}
		return $this->connection->affected_rows;
	}

	//--------------------------------------------------------------------------- getStoredProperties
	/**
	 * Returns the list of properties of class $class that are stored into data link
	 *
	 * If data link stores properties not existing into $class, they are listed too,
	 * as if they where official properties of $class, but they storage object is a Sql\Column
	 * and not a Reflection_Property.
	 *
	 * @param $class Reflection_Class
	 * @return Reflection_Property[]|Column[] key is the name of the property
	 */
	public function getStoredProperties($class)
	{
		$properties = $class->getProperties([T_EXTENDS, T_USE]);
		foreach ($properties as $key => $property) {
			if (!Store_Annotation::of($property)->isJson()) {
				$type = $property->getType();
				if (
					$property->isStatic()
					|| ($type->isMultiple() && !$type->getElementType()->isBasic())
					|| $property->getAnnotation('component')->value
				) {
					unset($properties[$key]);
				}
				elseif ($type->isClass()) {
					$properties[$property->name] = new Column(
						'id_' . Store_Name_Annotation::of($property)->value
					);
				}
			}
		}
		return $properties;
	}

	//------------------------------------------------------------------------------------ lockRecord
	/**
	 * Create an exclusive access to some code based on a table name and a record is
	 *
	 * The lock is linked to the current mysql thread : if a thread is not active anymore, the
	 * matching stored locks are considered as done and unlocked
	 *
	 * @param $table_name        string
	 * @param $record_identifier integer
	 * @param $options           string[] @values Lock::const
	 * @return Lock|null Lock if has been locked, null if could not lock (always Lock if Lock::WAIT)
	 */
	public function lockRecord(
		$table_name, $record_identifier, array $options = [Lock::WAIT, Lock::WRITE]
	) {
		$lock_key = $table_name . DOT . $record_identifier;
		if (isset($this->locks[$lock_key])) {
			$lock = $this->locks[$lock_key];
			$lock->count ++;
		}
		else {
			$this->begin();
			$this->query('LOCK TABLES `locks` WRITE');
			$duration = 1;
			while (
				($lock = Lock::get($table_name, $record_identifier, $this))
				&& in_array(Lock::WAIT, $options)
			) {
				$this->query('UNLOCK TABLES');
				$this->commit();
				usleep($duration * 10000);
				$this->begin();
				$this->query('LOCK TABLES `locks` WRITE');
				$duration = min(99 + rand(0, 2), $duration + rand(1, 10));
			}
			if ($lock) {
				$lock = null;
			}
			else {
				$lock   = new Lock($table_name, $record_identifier, $options);
				$write  = Sql\Builder::getObjectVars($lock);
				unset($write['count']);
				$insert          = Sql\Builder::buildInsert(Lock::class, $write);
				$lock_identifier = $this->query($insert);
				$this->setObjectIdentifier($lock, $lock_identifier);
				$this->locks[$lock_key] = $lock;
			}
			$this->query('UNLOCK TABLES');
			$this->commit();
		}
		return $lock;
	}

	//------------------------------------------------------------------------------------ popContext
	/**
	 * Pop context for sql query
	 */
	public function popContext()
	{
		return array_pop($this->connection->contexts);
	}

	//---------------------------------------------------------------------------------- prepareFetch
	/**
	 * Prepare fetch gets annotations values that transform the read object
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 */
	private function prepareFetch($class_name)
	{
		$this->prepared_fetch = [];
		/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
		foreach ((new Reflection_Class($class_name))->getProperties([T_EXTENDS, T_USE]) as $property) {
			// dao must be before gz : first we get the value from the file, then we inflate it
			if ($dao = $property->getAnnotation('dao')->value) {
				$dao = Dao::get($dao);
				if ($dao !== $this) {
					$this->prepared_fetch[$property->name][] = $dao;
				}
			}
			if (Store_Annotation::of($property)->isGz()) {
				$this->prepared_fetch[$property->name][] = self::GZINFLATE;
			}
		}
	}

	//----------------------------------------------------------------------------------- pushContext
	/**
	 * Push context for sql query
	 *
	 * @param $context_object string|string[] Can be a class name or an array of class names
	 */
	public function pushContext($context_object)
	{
		array_push($this->connection->contexts, $context_object);
	}

	//----------------------------------------------------------------------------------------- query
	/**
	 * Executes an SQL query and returns the inserted record identifier or the mysqli result object
	 *
	 * - if $class_name is set and $query is a 'SELECT', returned value will be an object[]
	 * - if $query is a 'SELECT' without $class_name, then returned value will be a mysqli_result
	 * - if $query is an 'INSERT' returned value will be the last insert id
	 * - other cases : returned value make no sense : do not use it ! (may be null or last insert id)
	 *
	 * @param $query string
	 * @param $class_name string if set, the result will be object[] with read data
	 * @param $result mixed The result set associated to the data link, if $class_name is constant
	 *        Call $query with $result = true to store the result set into $result
	 * @return mixed|mysqli_result depends on $class_name specific constants used
	 */
	public function query($query, $class_name = null, &$result = null)
	{
		$get_result = $result;
		if ($query) {
			$result = $this->connection->query($query);
			if (isset($class_name)) {
				if ($class_name === AS_ARRAY) {
					$objects = $this->queryFetchAsArray($result);
				}
				elseif ($class_name === AS_VALUE) {
					$objects = $this->queryFetchAsValue($result);
				}
				elseif ($class_name === AS_VALUES) {
					$objects = $this->queryFetchAsValues($result);
				}
				else {
					$objects = $this->queryFetchAsObjects($result, $class_name);
					$this->afterReadMultiple($objects);
				}
				if (!$get_result) {
					$result->free();
				}
			}
			else {
				$objects = $this->connection->isSelect($query) ? $result : $this->connection->insert_id;
			}
		}
		else {
			$objects = null;
		}
		if (!$get_result) {
			$result = null;
		}
		return $objects;
	}

	//----------------------------------------------------------------------------- queryFetchAsArray
	/**
	 * @param $result mysqli_result
	 * @return array [$id|$n => [$key => $value]] each element is an [$key => $value] record array
	 */
	private function queryFetchAsArray(mysqli_result $result)
	{
		$elements = [];
		while ($element = $result->fetch_assoc()) {
			$elements[] = $element;
		}
		return $elements;
	}

	//--------------------------------------------------------------------------- queryFetchAsObjects
	/**
	 * @param $result     mysqli_result
	 * @param $class_name string
	 * @return object[] [$id|$n => $object] each element is a new instance of class $class_name
	 */
	private function queryFetchAsObjects(mysqli_result $result, $class_name)
	{
		$objects    = [];
		$class_name = Builder::className($class_name);
		while ($object = $result->fetch_object($class_name)) {
			if (isset($object->id)) {
				$objects[$object->id] = $object;
			}
			else {
				$objects[] = $object;
			}
		}
		return $objects;
	}

	//----------------------------------------------------------------------------- queryFetchAsValue
	/**
	 * TODO not tested : the first value will probably be 'id'. But is the last value right ?
	 *
	 * @param $result mysqli_result
	 * @return mixed the value of the returned column for the first read row
	 */
	private function queryFetchAsValue(mysqli_result $result)
	{
		$element = $result->fetch_row();
		return $element ? $element[$result->field_count - 1] : null;
	}

	//---------------------------------------------------------------------------- queryFetchAsValues
	/**
	 * @param $result mysqli_result
	 * @return array [$id|$n => $value] each element is the value of the first returned column
	 */
	private function queryFetchAsValues(mysqli_result $result)
	{
		$values  = [];
		$element = $result->fetch_assoc();
		if (!$element) {
			return $values;
		}
		if (isset($element['id'])) {
			if (count($element) > 1) {
				do {
					$id = $element['id'];
					unset($element['id']);
					$values[$id] = reset($element);
				} while ($element = $result->fetch_assoc());
			}
			else {
				do {
					$id = $element['id'];
					$values[$id] = $id;
				}
				while ($element = $result->fetch_assoc());
			}
		}
		else {
			do {
				$values[] = reset($element);
			}
			while ($element = $result->fetch_assoc());
		}
		return $values;
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from data source
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $identifier integer|object identifier for the object, or an object to re-read
	 * @param $class_name string class for read object. Useless if $identifier is an object
	 * @return object an object of class objectClass, read from data source, or null if nothing found
	 */
	public function read($identifier, $class_name = null)
	{
		if (is_object($identifier)) {
			return $this->read(Dao::getObjectIdentifier($identifier), get_class($identifier));
		}
		if (!$identifier) {
			return null;
		}
		$class_name = Builder::className($class_name);
		array_push($this->connection->contexts, $class_name);
		/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
		if (Class_\Link_Annotation::of(new Reflection_Class($class_name))->value) {
			$what = [];
			foreach (explode(Link_Class::ID_SEPARATOR, $identifier) as $identify) {
				if (!strpos($identify, '=')) {
					trigger_error(
						'Bad link object identifier ' . $identifier . ' for link class ' . $class_name,
						E_USER_ERROR
					);
				}
				list($column, $value) = explode('=', $identify);
				$what[$column]        = $value;
			}
			$object = $this->searchOne($what, $class_name);
		}
		else {
			// it's for optimisation purpose only
			$query = 'SELECT *' . LF
				. 'FROM ' . BQ . $this->storeNameOf($class_name) . BQ . LF
				. 'WHERE id = ' . intval($identifier);
			$result_set = $this->connection->query($query);
			$this->prepareFetch($class_name);
			$object = $this->fetch($result_set, $class_name);
			$this->free($result_set);
		}
		array_pop($this->connection->contexts);
		if ($object) {
			$this->setObjectIdentifier($object, $identifier);
			$this->afterRead($object);
		}
		return $object;
	}

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * Read all objects of a given class from data source
	 *
	 * @param $class_name string class for read objects
	 * @param $options    Option|Option[] some options for advanced read
	 * @return object[] a collection of read objects
	 */
	public function readAll($class_name, $options = [])
	{
		if (!is_array($options)) {
			$options = $options ? [$options] : [];
		}
		$class_name = Builder::className($class_name);
		array_push($this->connection->contexts, $class_name);
		$query      = (new Select($class_name, null, null, null, $options))->buildQuery();
		$result_set = $this->connection->query($query);
		if ($options) {
			$this->getRowsCount('SELECT', $options, $result_set);
		}
		$objects = $this->fetchAll($class_name, $options, $result_set);
		array_pop($this->connection->contexts);
		$this->afterReadMultiple($objects, $options);
		return $objects;
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
		$table_name     = $this->storeNameOf(get_class($replaced));
		$replaced_id    = $this->getObjectIdentifier($replaced);
		$replacement_id = $this->getObjectIdentifier($replacement);
		if ($replaced_id && $replacement_id && $table_name) {
			foreach (Foreign_Key::buildReferences($this->connection, $table_name) as $foreign_key) {
				$foreign_table_name = lParse($foreign_key->getConstraint(), DOT);
				$foreign_field_name = $foreign_key->getFields()[0];
				$query = 'UPDATE ' . BQ . $foreign_table_name . BQ
					. LF . 'SET ' . BQ . $foreign_field_name . BQ . ' = ' . $replacement_id
					. LF . 'WHERE ' . BQ . $foreign_field_name . BQ . ' = ' . $replaced_id;
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
	 * @return boolean|null true if commit succeeds, false if error, null if not a transactional
	 *                      SQL engine
	 */
	public function rollback()
	{
		$this->commit_stack = 0;
		if (isset($GLOBALS['D'])) {
			trigger_error('Rollback', E_USER_NOTICE);
			$this->commit_stack_trace = [];
		}
		$this->query('ROLLBACK');
		return true;
	}

	//---------------------------------------------------------------------------------------- search
	/**
	 * Search objects from data source
	 *
	 * It is highly recommended to instantiate the $what object using Search_Object::instantiate() in
	 * order to initialize all properties as unset and build a correct search object.
	 * If some properties are an not-loaded objects, the search will be done on the object identifier,
	 * without joins to the linked object.
	 * If some properties are loaded objects : if the object comes from a read, the search will be
	 * done on the object identifier, without join. If object is not linked to data-link, the search
	 * is done with the linked object as others search criterion.
	 *
	 * @param $what       object|array source object for filter, or filter array (need class_name)
	 *                    only set properties will be used for search
	 * @param $class_name string must be set if $what is a filter array and not an object
	 * @param $options    Option|Option[] some options for advanced search
	 * @return object[] a collection of read objects
	 */
	public function search($what, $class_name = null, $options = [])
	{
		// prepare arguments
		if (!is_array($options)) {
			$options = $options ? [$options] : [];
		}
		if (!isset($class_name)) {
			$class_name = get_class($what);
		}
		$class_name = Builder::className($class_name);

		// read result from cache
		$cache_result = Cache_Result::get($options);
		$objects = $cache_result ? $cache_result->cachedResult($what, $class_name, $options) : null;
		if (!isset($objects)) {
			// was not in cache or no cache : prepare and execute query
			$builder = new Select($class_name, null, $what, $this, $options);
			$query   = $builder->buildQuery();
			array_push($this->connection->contexts, $builder->getJoins()->getClassNames());
			if (Option\Pre_Load::in($options)) {
				$objects = [];
				(new Dao\Sql\Select($class_name, null, $this))->executeQuery($query);
			}
			else {
				$result_set = $this->connection->query($query);
				if ($options) {
					$this->getRowsCount('SELECT', $options, $result_set);
				}
				$objects = $this->fetchAll($class_name, $options, $result_set);
			}
			array_pop($this->connection->contexts);
			// store result in cache
			if ($cache_result) {
				$cache_result->cacheResult($what, $class_name, $options, $objects);
			}
		}

		// after read
		$this->afterReadMultiple($objects, $options);
		if (!$objects && ($created = $this->createObjectIfOption($what, $class_name, $options))) {
			$objects = [$created];
		}
		return $objects;
	}

	//---------------------------------------------------------------------------------------- unlock
	/**
	 * @param $lock Lock
	 * @see lockRecord
	 */
	public function unlock(Lock $lock)
	{
		$lock_key = $lock->table_name . DOT . $lock->identifier;
		if (isset($this->locks[$lock_key])) {
			$lock = $this->locks[$lock_key];
			$lock->count --;
			if (!$lock->count) {
				unset($this->locks[$lock_key]);
				$this->delete($lock);
			}
		}
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
	 * TODO LOWEST factorize this to become SOLID
	 *
	 * @param $object  object object to write into data source
	 * @param $options Option|Option[] some options for advanced write
	 * @return object the written object if written, or null if the object could not be written
	 */
	public function write($object, $options = [])
	{
		if (!is_array($options)) {
			$options = $options ? [$options] : [];
		}
		return (new Write($this, $object, $options))->run();
	}

}
