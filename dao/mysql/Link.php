<?php
namespace ITRocks\Framework\Dao\Mysql;

use Exception;
use ITRocks\Framework\Reflection\Annotation\Property\Foreign_Annotation;
use ITRocks\Framework\Tools\String_Class;
use mysqli_result;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Event\Property_Add;
use ITRocks\Framework\Dao\Event\Property_Remove;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Mapper\Abstract_Class;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Mapper\Empty_Object;
use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\Mapper\Null_Object;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql;
use ITRocks\Framework\Sql\Builder\Count;
use ITRocks\Framework\Sql\Builder\Map_Delete;
use ITRocks\Framework\Sql\Builder\Map_Insert;
use ITRocks\Framework\Sql\Builder\Select;
use ITRocks\Framework\Tools\Contextual_Mysqli;

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

	//----------------------------------------------------------------------------------- $connection
	/**
	 * Connection to the mysqli server is a mysqli object
	 *
	 * @var Contextual_Mysqli
	 */
	private $connection;

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
		$this->connect($parameters);
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
			$this->query('START TRANSACTION');
		}
		$this->commit_stack ++;
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

	//---------------------------------------------------------------------------------------- commit
	/**
	 * @param $flush boolean if true, then all the pending transactions will be unstacked
	 * @return boolean
	 */
	public function commit($flush = false)
	{
		if ($flush) {
			$this->commit_stack = 0;
			$this->query('COMMIT');
		}
		elseif ($this->commit_stack > 0) {
			$this->commit_stack --;
			if (!$this->commit_stack) {
				$this->query('COMMIT');
			}
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
			$parameters[self::HOST],     $parameters[self::LOGIN],
			$parameters[self::PASSWORD], $parameters[self::DATABASE]
		);
		$this->query('SET NAMES ' . $this->collation);
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * Count the number of elements that match filter
	 *
	 * @param $what       object|string|array source object, class name or properties for filter
	 * @param $class_name string must be set if is $what is a filter array instead of a filter object
	 * @return integer
	 */
	public function count($what, $class_name = null)
	{
		if (is_string($what)) {
			$class_name = $what;
			$what       = null;
		}
		$class_name = Builder::className($class_name);
		$builder = new Count($class_name, $what, $this);
		$query = $builder->buildQuery();
		$this->setContext($builder->getJoins()->getClassNames());
		$result_set = $this->connection->query($query);
		if ($result_set) {
			$row = $result_set->fetch_row();
			$result_set->free();
		}
		else {
			$row = [0];
		}
		return $row[0];
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
	 * @param $object object object to delete from data source
	 * @return boolean true if deleted
	 * @see Data_Link::delete()
	 */
	public function delete($object)
	{
		$will_delete = true;
		foreach (
			(new Reflection_Class(get_class($object)))->getAnnotations('before_delete') as $before_delete
		) {
			/** @var $before_delete Method_Annotation */
			if ($before_delete->call($object, [$this]) === false) {
				$will_delete = false;
				break;
			}
		}

		if ($will_delete) {
			$id = $this->getObjectIdentifier($object);
			if ($id) {
				$class_name         = get_class($object);
				$class              = new Reflection_Class($class_name);
				$link               = Class_\Link_Annotation::of($class);
				$exclude_properties = $link->value
					? array_keys((new Reflection_Class($link->value))->getProperties([T_EXTENDS, T_USE]))
					: [];
				foreach ($class->accessProperties() as $property) {
					if (!$property->isStatic() && !in_array($property->name, $exclude_properties)) {
						if (Link_Annotation::of($property)->isCollection()) {
							if ($property->getType()->isMultiple()) {
								$this->deleteCollection($object, $property, $property->getValue($object));
							}
							// TODO dead code ? @link Collection is only for @var object[], not for @var object
							else {
								$this->delete($property->getValue($object));
								trigger_error(
									"Dead code into Mysql\\Link::delete() on {$property->name} is not so dead",
									E_USER_NOTICE
								);
							}
						}
					}
				}
				$this->setContext($class_name);
				if ($link->value) {
					$id = [];
					foreach ($link->getLinkClass()->getUniqueProperties() as $link_property) {
						$property_name = $link_property->getName();
						if (Dao::storedAsForeign($link_property)) {
							$column_name = 'id_' . $link_property->getAnnotation('storage')->value;
							$id[$column_name] = $this->getObjectIdentifier($object, $property_name);
						}
						else {
							$column_name = $link_property->getAnnotation('storage')->value;
							$id[$column_name] = $link_property->getValue($object);
						}
					}
				}
				$this->query(Sql\Builder::buildDelete($class_name, $id));
				$this->disconnect($object);
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
	 * @param $value string|object
	 * @return string
	 */
	public function escapeString($value)
	{
		if (is_object($value)) {
			$id = $this->getObjectIdentifier($value, 'id');
			$properties = (new Reflection_Class(get_class($value)))->getAnnotedProperties(
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
		$object = $result_set->fetch_object(Builder::className($class_name));
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
		$search_result = [];
		$keys = $this->getKeyPropertyName($options);
		if (($keys !== 'id') && isset($keys)) {
			if (is_array($keys)) {
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
		$fetch_class_name = ((new Reflection_Class($class_name))->isAbstract())
			? Abstract_Class::class
			: $class_name;
		$this->prepareFetch($fetch_class_name);
		while ($object = $this->fetch($result_set, $fetch_class_name)) {
			$this->setObjectIdentifier($object, $object->id);
			// the most common key is the record id : do it quick
			if ($keys === 'id') {
				$search_result[$object->id] = $object;
			}
			// complex keys
			elseif (isset($keys) && isset($object_key)) {
				// result key must be a set of several id keys (used for linked classes collections)
				// (Dao::key(['property_1', 'property_2']))
				if (is_array($object_key)) {
					$k_key = '';
					$multiple = count($keys) > 1;
					foreach ($keys as $key => $k_keys) {
						$k_object_key = $object_key[$key];
						$key_object   = $object;
						foreach ($k_keys as $key) {
							$key_object = $key_object->$key;
						}
						$k_id_object_key = 'id_' . $k_object_key;
						$k_key .= ($k_key ? Link_Class::ID_SEPARATOR : '')
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
					foreach ($keys as $key) $key_object = $key_object->$key;
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
	 * Link classes objects identifiers are the identifiers of all their @composite properties values
	 * also known as link properties values.
	 *
	 * @param $object object
	 * @param $link   Class_\Link_Annotation send it for optimization, but this is not mandatory
	 * @return string identifiers in a single string, separated with '.'
	 */
	private function getLinkObjectIdentifier($object, Class_\Link_Annotation $link = null)
	{
		if (!isset($link)) {
			$link = Class_\Link_Annotation::of(new Reflection_Class(get_class($object)));
		}
		if ($link->value) {
			$ids = [];
			$link_class = $link->getLinkClass();
			foreach ($link_class->getUniqueProperties() as $link_property) {
				$property_name = $link_property->getName();
				if (Dao::storedAsForeign($link_property)) {
					$id = parent::getObjectIdentifier($object, $property_name);
					if (!isset($id)) {
						if ($link_class->getCompositeProperty()->name == $property_name) {
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
		else {
			if ($clause == 'SELECT') {
				$result = $this->connection->query('SELECT FOUND_ROWS()');
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
			if ($property->getAnnotation(Store_Annotation::ANNOTATION) != Store_Annotation::JSON) {
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
						'id_' . $property->getAnnotation('storage')->value
					);
				}
			}
		}
		return $properties;
	}

	//---------------------------------------------------------------------------- objectToWriteArray
	/**
	 * Gets write arrays from an object
	 *
	 * TODO Factorize : this method is still too big
	 *
	 * @param $object  object
	 * @param $options Option\Spreadable[] Spread options
	 * @param $only    string[] get write arrays for these properties only (if set)
	 * @param $exclude string[] get write arrays for these properties only (if set)
	 * @param $class   Link_Class
	 * @return array [$write, $write_collections, $write_maps, $write_objects, $write_properties]
	 * @throws Exception
	 */
	private function objectToWriteArray(
		$object, array $options, array $only = null, array $exclude = [], Link_Class $class = null
	) {
		if (!$class) {
			$class = new Link_Class(get_class($object));
		}
		$link                = Class_\Link_Annotation::of($class);
		$table_columns_names = array_keys($this->getStoredProperties($class));
		$write_collections   = [];
		$write_maps          = [];
		$write_objects       = [];
		$write_properties    = [];
		$write               = [];
		$aop_getter_ignore   = Getter::$ignore;
		Getter::$ignore      = true;
		$exclude_properties  = $link->value
			? array_keys((new Reflection_Class($link->value))->getProperties([T_EXTENDS, T_USE]))
			: [];
		/** @var $properties Reflection_Property[] */
		$properties = $class->accessProperties();
		$properties = Replaces_Annotations::removeReplacedProperties($properties);
		foreach ($properties as $property) {
			$property_name = $property->name;
			if (
				(!isset($only) || in_array($property_name, $only))
				&& !in_array($property_name, $exclude)
			) {
				if (
					!$property->isStatic()
					&& !in_array($property_name, $exclude_properties)
					&& (
						$property->getAnnotation(Store_Annotation::ANNOTATION)->value
						!== Store_Annotation::FALSE
					)
				) {
					$value            = isset($object->$property_name) ? $property->getValue($object) : null;
					$property_is_null = $property->getAnnotation('null')->value;
					if (is_null($value) && !$property_is_null) {
						$value = '';
					}
					if (in_array($property_name, $table_columns_names)) {
						$element_type = $property->getType()->getElementType();
						$storage_name = $property->getAnnotation('storage')->value;
						// write basic
						if ($element_type->isBasic(false)) {
							if (
								$element_type->isString()
								&& in_array(
									$property->getAnnotation(Store_Annotation::ANNOTATION)->value,
									[Store_Annotation::GZ, Store_Annotation::HEX]
								)
							) {
								if (
									$property->getAnnotation(Store_Annotation::ANNOTATION)->value
									=== Store_Annotation::GZ
								) {
									$value = gzdeflate($value);
								}
								$will_hex = true;
							}
							else {
								$values = $property->getListAnnotation('values')->values();
								if (is_array($value)) {
									$value = ($property->getType()->isMultipleString() && $values)
										? join(',', $value)
										: json_encode($value);
								}
								$write[$storage_name] = $values ? new String_Class($value) : $value;
							}
							if ($dao = $property->getAnnotation('dao')->value) {
								if (($dao = Dao::get($dao)) !== $this) {
									$write_properties[]   = [$property->name, $value, $dao];
									$write[$storage_name] = '';
									if (isset($will_hex)) {
										unset($will_hex);
									}
								}
							}
							if (isset($will_hex)) {
								if (strlen($value)) {
									$write[$storage_name] = 'X' . Q . bin2hex($value) . Q;
								}
								unset($will_hex);
							}
						}
						// write array or object into a @store gz/hex/string
						elseif ($store = $property->getAnnotation(Store_Annotation::ANNOTATION)->value) {
							if ($store == Store_Annotation::JSON) {
								$value = $this->valueToWriteArray($value, $options);
								if (isset($value) && !is_string($value)) {
									$value = json_encode($value);
								}
							}
							else {
								$value = is_array($value) ? serialize($value) : strval($value);
							}
							if ($store === Store_Annotation::GZ) {
								$value = 'X' . Q . bin2hex(gzdeflate($value)) . Q;
							}
							elseif ($store === Store_Annotation::HEX) {
								$value = 'X' . Q . bin2hex($value) . Q;
							}
							$write[$storage_name] = $value;
							if ($dao = $property->getAnnotation('dao')->value) {
								if (($dao = Dao::get($dao)) !== $this) {
									$write_properties[]   = [$property->name, $write[$storage_name], $dao];
									$write[$storage_name] = '';
								}
							}
						}
						// write object id if set or object if no id is set (new object)
						else {
							$id_column_name = 'id_' . $property_name;
							if (is_object($value)) {
								$value_class = new Link_Class(get_class($value));
								$id_value    = (
									$value_class->getLinkedClassName()
									&& !Class_\Link_Annotation::of($element_type->asReflectionClass())->value
								) ? 'id_' . $value_class->getCompositeProperty()->name
									: 'id';
								$object->$id_column_name = $this->getObjectIdentifier($value, $id_value);
								if (empty($object->$id_column_name)) {
									Getter::$ignore = $aop_getter_ignore;
									if (!isset($value) || isA($element_type->asString(), get_class($value))) {
										$object->$id_column_name = $this->getObjectIdentifier(
											$this->write($value, $options), $id_value
										);
									}
									else {
										$clone = Builder::createClone($value, $property->getType()->asString());
										$object->$id_column_name = $this->getObjectIdentifier(
											$this->write($clone, $options), $id_value
										);
										$this->replace($value, $clone, false);
									}
									Getter::$ignore = true;
								}
							}
							$write['id_' . $storage_name] = (
								($property_is_null && !isset($object->$id_column_name))
								? null
								: intval($object->$id_column_name)
							);
						}
					}
					// write collection
					elseif (is_array($value) && Link_Annotation::of($property)->isCollection()) {
						$write_collections[] = [$property, $value];
					}
					// write map
					elseif (is_array($value) && Link_Annotation::of($property)->isMap()) {
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
					// write object
					elseif (
						Link_Annotation::of($property)->isObject()
						&& $property->getAnnotation('component')->value
					) {
						$write_objects[] = [$property, $value];
					}
				}
			}
		}
		Getter::$ignore = $aop_getter_ignore;
		return [$write, $write_collections, $write_maps, $write_objects, $write_properties];
	}

	//---------------------------------------------------------------------------------- prepareFetch
	/**
	 * Prepare fetch gets annotations values that transform the read object
	 *
	 * @param $class_name string
	 */
	private function prepareFetch($class_name)
	{
		$this->prepared_fetch = [];
		foreach ((new Reflection_Class($class_name))->getProperties([T_EXTENDS, T_USE]) as $property) {
			// dao must be before gz : first we get the value from the file, then we inflate it
			if ($dao = $property->getAnnotation('dao')->value) {
				$dao = Dao::get($dao);
				if ($dao !== $this) {
					$this->prepared_fetch[$property->name][] = $dao;
				}
			}
			if ($property->getAnnotation(Store_Annotation::ANNOTATION)->value === Store_Annotation::GZ) {
				$this->prepared_fetch[$property->name][] = self::GZINFLATE;
			}
		}
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
			if (isset($element['id'])) {
				$elements[$element['id']] = $element;
			}
			else {
				$elements[] = $element;
			}
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
		$objects = [];
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
	 * TODO not tested : the first value will probably be 'id'. But is the last value right ?
	 *
	 * @param $result mysqli_result
	 * @return array [$id|$n => $value] each element is the value of the first returned column
	 */
	private function queryFetchAsValues(mysqli_result $result)
	{
		$column = $result->field_count - 1;
		$values = [];
		while ($element = $result->fetch_row()) {
			if (isset($element['id'])) {
				$values[$element['id']] = $element[$column];
			}
			else {
				$values[] = $element[$column];
			}
		}
		return $values;
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from data source
	 *
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
		$this->setContext($class_name);
		if (Class_\Link_Annotation::of(new Reflection_Class($class_name))->value) {
			$what = [];
			foreach (explode(Link_Class::ID_SEPARATOR, $identifier) as $identify) {
				list($column, $value) = explode('=', $identify);
				$what[$column] = $value;
			}
			$object = $this->searchOne($what, $class_name);
		}
		else {
			// it's for optimisation purpose only
			$query = 'SELECT *'
				. LF . 'FROM' . SP . BQ . $this->storeNameOf($class_name) . BQ
				. LF . 'WHERE id = ' . $identifier;
			$result_set = $this->connection->query($query);
			$this->prepareFetch($class_name);
			$object = $this->fetch($result_set, $class_name);
			$this->free($result_set);
		}
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
		$this->setContext($class_name);
		$query = (new Select($class_name, null, null, null, $options))->buildQuery();
		$result_set = $this->connection->query($query);
		if ($options) {
			$this->getRowsCount('SELECT', $options, $result_set);
		}
		$objects = $this->fetchAll($class_name, $options, $result_set);
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
		$table_name = $this->storeNameOf(get_class($replaced));
		$replaced_id = $this->getObjectIdentifier($replaced);
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
	 * @return boolean|null true if commit succeeds, false if error, null if not a transactional SQL engine
	 */
	public function rollback()
	{
		$this->commit_stack = 0;
		$this->query('ROLLBACK');
		return true;
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
	 * @param $options    Option|Option[] some options for advanced search
	 * @return object[] a collection of read objects
	 */
	public function search($what, $class_name = null, $options = [])
	{
		if (!is_array($options)) {
			$options = $options ? [$options] : [];
		}
		if (!isset($class_name)) {
			$class_name = get_class($what);
		}
		$class_name = Builder::className($class_name);
		$builder    = new Select($class_name, null, $what, $this, $options);
		$query      = $builder->buildQuery();
		$this->setContext($builder->getJoins()->getClassNames());
		$result_set = $this->connection->query($query);
		if ($options) {
			$this->getRowsCount('SELECT', $options, $result_set);
		}
		$objects = $this->fetchAll($class_name, $options, $result_set);
		$this->afterReadMultiple($objects, $options);
		return $objects;
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

	//----------------------------------------------------------------------------- valueToWriteArray
	/**
	 * Prepare a property value for JSON encode
	 *
	 * @param $value   mixed The value of a property
	 * @param $options Option\Spreadable[] Spread options
	 * @return array
	 */
	protected function valueToWriteArray($value, array $options)
	{
		$array = [];
		if (is_object($value)) {
			// encode only stored data and map, not collection
			list($array, , $maps) = $this->objectToWriteArray($value, $options);
			// JSON comes first, like it is done by serialize()
			$array = array_merge([Store_Annotation::JSON_CLASS => get_class($value)], $array);
			foreach ($maps as list($property, $values)) {
				/** @var $property Reflection_Property */
				foreach ($values as $key => $value) {
					if (Dao::getObjectIdentifier($value)) {
						$array[$property->name][$key] = Dao::getObjectIdentifier($value);
					}
				}
			}
		}
		elseif (is_array($value)) {
			foreach ($value as $key => $sub_value) {
				$array[$key] = $this->valueToWriteArray($sub_value, $options);
			}
		}
		else {
			$array = $value;
		}
		return $array;
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
		if ($this->beforeWrite($object, $options)) {
			if (Null_Object::isNull($object, [Store_Annotation::class, 'storedPropertiesOnly'])) {
				$this->disconnect($object);
			}
			$class          = new Link_Class(get_class($object));
			$exclude        = [];
			$id_property    = 'id';
			$only           = null;
			$spread_options = [];
			foreach ($options as $option) {
				if ($option instanceof Option\Add) {
					$force_add = true;
				}
				elseif ($option instanceof Option\Exclude) {
					$exclude = array_merge($exclude, $option->properties);
				}
				elseif ($option instanceof Option\Only) {
					$only = isset($only)
						? array_merge($only, $option->properties)
						: $option->properties;
				}
				elseif ($option instanceof Option\Link_Class_Only) {
					$link_class_only = true;
				}
				if ($option instanceof Option\Spreadable) {
					$spread_options[] = $option;
				}
			}
			do {
				$link = Class_\Link_Annotation::of($class);
				if ($link->value) {
					$link_property = $link->getLinkClass()->getLinkProperty();
					$link_object   = $link_property->getValue($object);
					if (!$link_object) {
						$id_link_property          = 'id_' . $link_property->name;
						$object->$id_link_property = $this->write($link_object, $options);
					}
				}
				list($write, $write_collections, $write_maps, $write_objects, $write_properties)
					= $this->objectToWriteArray($object, $spread_options, $only, $exclude, $class);

				/** @var $properties Reflection_Property[] */
				$properties = $class->accessProperties();
				$properties = Replaces_Annotations::removeReplacedProperties($properties);
				if ($write) {
					// link class : id is the couple of composite properties values
					if ($link->value) {
						$search = [];
						foreach ($link->getLinkClass()->getUniqueProperties() as $property) {
							/** @var $property Reflection_Property $link annotates a Reflection_Property */
							$property_name = $property->getName();
							$column_name   = Dao::storedAsForeign($property) ? 'id_' : '';
							$column_name  .= $properties[$property_name]->getAnnotation('storage')->value;
							if (isset($write[$column_name])) {
								$search[$property_name] = $write[$column_name];
							}
							elseif (isset($write[$property_name])) {
								$search[$property_name] = $write[$column_name];
							}
							else {
								trigger_error("Can't search $property_name", E_USER_ERROR);
							}
						}
						if ($this->search($search, $class->name)) {
							$id = [];
							foreach ($search as $property_name => $value) {
								$column_name = $properties[$property_name]->getAnnotation('storage')->value;
								if (isset($write['id_' . $column_name])) {
									$column_name = 'id_' . $column_name;
								}
								$id[$column_name] = $value;
								unset($write[$column_name]);
							}
						}
						else {
							$id = null;
						}
					}
					// standard class : get the property 'id' value
					else {
						$id = $this->getObjectIdentifier($object, $id_property);
					}
					if ($write) {
						$this->setContext($class->name);
						if (empty($id) || isset($force_add)) {
							$this->disconnect($object);
							if (isset($force_add) && !empty($id)) {
								$write['id'] = $id;
							}
							$id = $this->query(Sql\Builder::buildInsert($class->name, $write));
							if (!empty($id)) {
								$this->setObjectIdentifier($object, $id);
							}
						}
						else {
							$this->query(Sql\Builder::buildUpdate($class->name, $write, $id));
						}
					}
				}
				foreach ($write_collections as $write) {
					list($property, $value) = $write;
					$this->writeCollection($object, $spread_options, $property, $value);
				}
				foreach ($write_maps as $write) {
					list($property, $value) = $write;
					$this->writeMap($object, $spread_options, $property, $value);
				}
				foreach ($write_objects as $write) {
					list($property, $value) = $write;
					$this->writeObject($object, $spread_options, $property, $value);
				}
				foreach ($write_properties as $write) {
					/** @var $dao Data_Link */
					list($property, $value, $dao) = $write;
					$dao->writeProperty($object, $property, $value);
				}
				// if link class : write linked object too
				$id_property = $link->value ? ('id_' . $class->getCompositeProperty()->name) : null;
				$class       = $link->value ? new Link_Class($link->value) : null;
			} while (
				$class
				&& !isset($link_class_only)
				&& !Null_Object::isNull($object, function($properties) use ($class) {
					return Store_Annotation::storedPropertiesOnly(
						Reflection_Property::filter($properties, $class->name)
					);
				})
			);

			/** @var $after_writes Method_Annotation[] */
			$after_writes = (new Reflection_Class(get_class($object)))->getAnnotations('after_write');
			foreach ($after_writes as $after_write) {
				if ($after_write->call($object, [$this, &$options]) === false) {
					break;
				}
			}

			return $object;
		}
		return null;
	}

	//------------------------------------------------------------------------------- writeCollection
	/**
	 * Write a component collection property value
	 *
	 * Ie when you write an order, it's implicitly needed to write its lines
	 *
	 * @param $object     object
	 * @param $options    Option\Spreadable[]
	 * @param $property   Reflection_Property
	 * @param $collection Component[]
	 */
	private function writeCollection(
		$object, array $options, Reflection_Property $property, array $collection
	) {
		// old collection
		$class_name = get_class($object);
		$old_object = Search_Object::create($class_name);
		$this->setObjectIdentifier($old_object, $this->getObjectIdentifier($object));
		$aop_getter_ignore = Getter::$ignore;
		Getter::$ignore    = false;
		$old_collection    = $property->getValue($old_object);
		Getter::$ignore    = $aop_getter_ignore;

		$element_class = $property->getType()->asReflectionClass();
		$element_link  = Class_\Link_Annotation::of($element_class);
		// collection properties : write each of them
		$id_set = [];
		if ($collection) {
			$options[]             = new Option\Link_Class_Only();
			$foreign_property_name = Foreign_Annotation::of($property)->value;
			foreach ($collection as $key => $element) {
				if (!is_a($element, $element_class->getName())) {
					$collection[$key] = $element = Builder::createClone($element, $element_class->getName(), [
						$element_link->getLinkClass()->getCompositeProperty()->name => $element
					]);
				}
				$element->setComposite($object, $foreign_property_name);
				$id = $element_link->value
					? $this->getLinkObjectIdentifier($element, $element_link)
					: $this->getObjectIdentifier($element);
				if (!empty($id)) {
					$id_set[$id] = true;
				}
				$old_element = ($id && isset($old_collection[$id])) ? $old_collection[$id] : null;
				if (!$old_element) {
					$property_add_event  = new Property_Add($this, $object, $element, $options, $property);
					$before_add_elements = $property->getAnnotations('before_add_element');
					$before_result       = $this->callEvent($property_add_event, $before_add_elements);
				}
				else {
					$property_add_event = null;
					$before_result      = true;
				}
				if ($before_result) {
					$this->write($element, empty($id) ? [] : $options);
					if ($property_add_event) {
						$after_add_elements = $property->getAnnotations('after_add_element');
						$this->callEvent($property_add_event, $after_add_elements);
					}
				}
			}
		}
		// remove old unused elements
		foreach ($old_collection as $old_element) {
			$id = $element_link->value
				? $this->getLinkObjectIdentifier($old_element, $element_link)
				: $this->getObjectIdentifier($old_element);
			if (!isset($id_set[$id])) {
				$remove_event = new Property_Remove($this, $object, $old_element, $options, $property);
				$before_remove_elements = $property->getAnnotations('before_remove_element');
				if ($this->callEvent($remove_event, $before_remove_elements)) {
					$this->delete($old_element);
				}
			}
		}
	}

	//-------------------------------------------------------------------------------------- writeMap
	/**
	 * @param $object   object
	 * @param $options  Option\Spreadable[]
	 * @param $property Reflection_Property
	 * @param $map      object[]
	 */
	private function writeMap($object, array $options, Reflection_Property $property, array $map)
	{
		// old map
		$class                   = new Link_Class(get_class($object));
		$composite_property_name = Class_\Link_Annotation::of($class)->value
			? $class->getCompositeProperty()->name
			: null;
		$old_object = Search_Object::create(Link_Class::linkedClassNameOf($object));
		$this->setObjectIdentifier(
			$old_object, $this->getObjectIdentifier($object, $composite_property_name)
		);
		$aop_getter_ignore = Getter::$ignore;
		Getter::$ignore = false;
		$old_map = $property->getValue($old_object);
		Getter::$ignore = $aop_getter_ignore;
		// map properties : write each of them
		$insert_builder = new Map_Insert($property);
		$id_set = [];
		foreach ($map as $element) {
			$id = $this->getObjectIdentifier($element)
				?: $this->getObjectIdentifier($this->write($element, $options));
			if (!isset($old_map[$id]) && !isset($id_set[$id])) {
				$property_add_event  = new Property_Add($this, $object, $element, $options, $property);
				$before_add_elements = $property->getAnnotations('before_add_element');
				if ($this->callEvent($property_add_event, $before_add_elements)) {
					$query = $insert_builder->buildQuery($object, $element);
					$this->connection->query($query);
					$after_add_elements = $property->getAnnotations('after_add_element');
					$this->callEvent($property_add_event, $after_add_elements);
				}
			}
			$id_set[$id] = true;
		}
		// remove old unused elements
		$delete_builder = new Map_Delete($property);
		foreach ($old_map as $old_element) {
			$id = $this->getObjectIdentifier($old_element);
			if (!isset($id_set[$id])) {
				$remove_event = new Property_Remove($this, $object, $old_element, $options, $property);
				$before_remove_elements = $property->getAnnotations('before_remove_element');
				if ($this->callEvent($remove_event, $before_remove_elements)) {
					$query = $delete_builder->buildQuery($object, $old_element);
					$this->connection->query($query);
				}
			}
		}
	}

	//----------------------------------------------------------------------------------- writeObject
	/**
	 * @param $object           object
	 * @param $options          Option\Spreadable[]
	 * @param $property         Reflection_Property
	 * @param $component_object Component
	 * @todo And what if $component_object has a @link class type ?
	 * @todo working with @dao property annotation
	 */
	private function writeObject(
		$object, array $options, Reflection_Property $property, $component_object
	) {
		// if there is already a stored component object : there must be only one
		if (is_object($component_object)) {
			$foreign_property_name = Foreign_Annotation::of($property)->value;
			$existing = $this->searchOne(
				[$foreign_property_name => $object], get_class($component_object)
			);
			if ($existing) {
				$this->replace($component_object, $existing, false);
			}
		}
		// delete
		if (Empty_Object::isEmpty($component_object)) {
			if ($this->getObjectIdentifier($component_object)) {
				Dao::delete($component_object);
			}
		}
		// create / update
		else {
			$component_object->setComposite($object);
			$this->write($component_object, $options);
		}
	}

}
