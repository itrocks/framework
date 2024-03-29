<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Data_Link\Identifier_Map;
use ITRocks\Framework\Dao\Data_Link\Transactional;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Reflection\Attribute\Property\Composite;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Current;
use ITRocks\Framework\Tools\List_Data;

/**
 * The Dao class enables direct access to the main Dao object of the application methods
 */
class Dao implements Configurable
{
	use Current { current as private pCurrent; }

	//------------------------------------------------------------------------------------ LINKS_LIST
	const LINKS_LIST = 'list';

	//----------------------------------------------------------------------------------------- $list
	/** @var Data_Link[] The list of available and referenced DAO */
	private static array $list;

	//----------------------------------------------------------------------------------- __construct
	/** @param $configuration array */
	public function __construct(mixed $configuration)
	{
		if (isset($configuration[self::LINKS_LIST])) {
			self::$list = $configuration[self::LINKS_LIST];
			unset($configuration[self::LINKS_LIST]);
		}
		$class_name = $configuration[Configuration::CLASS_NAME];
		unset($configuration[Configuration::CLASS_NAME]);
		Dao::current(new $class_name($configuration));
	}

	//------------------------------------------------------------------------------------------- add
	/** Forces add of object to the data link : no update, even if there is an identifier */
	public static function add() : Option\Add
	{
		return new Option\Add();
	}

	//----------------------------------------------------------------------------------------- begin
	/**
	 * Begin a transaction with the current data link (non-transactional SQL engines will do nothing
	 * and return null)
	 *
	 * @return ?boolean true if begin succeeds, false if error, null if not a transactional SQL engine
	 * @see Transactional::begin()
	 */
	public static function begin() : ?bool
	{
		$current = self::current();
		if ($current instanceof Transactional) {
			return $current->begin();
		}
		$current->after_commit = [];
		return null;
	}

	//----------------------------------------------------------------------------------- cacheResult
	/** Cache where options and query result and use cached result if exists */
	public static function cacheResult() : Option\Cache_Result
	{
		return new Option\Cache_Result();
	}

	//---------------------------------------------------------------------------------- classNamesOf
	/**
	 * Gets the class name associated to a store set name
	 *
	 * @return string[] Full class name with namespace
	 */
	public static function classNamesOf(string $store_name) : array
	{
		return self::current()->classNamesOf($store_name);
	}

	//---------------------------------------------------------------------------------------- commit
	/**
	 * Commit a transaction using the current data link (non-transactional SQL engines will do nothing
	 * and return null)
	 *
	 * @return ?boolean true if commit succeeds, false if error, null if not a transactional SQL
	 *                  engine
	 * @see Transactional::commit()
	 */
	public static function commit() : ?bool
	{
		$current = self::current();
		if ($current instanceof Transactional) {
			return $current->commit();
		}
		$current->afterCommit();
		return null;
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * Count the number of elements that match filter
	 *
	 * @param $what       array|object|string Source object for filter, only set properties used
	 * @param $class_name string|null         Must be set if is $what is a filter array instead of a
	 *                                        filter object
	 * @param $options    Option|Option[]     Some options for advanced search
	 * @return integer
	 */
	public static function count(
		array|object|string $what, string $class_name = null, array|Option $options = []
	) : int
	{
		return self::current()->count($what, $class_name, $options);
	}

	//------------------------------------------------------------------------------ createIfNoResult
	public static function createIfNoResult() : Option\Create_If_No_Result
	{
		return new Option\Create_If_No_Result();
	}

	//--------------------------------------------------------------------------------- createStorage
	/**
	 * Create a storage space for $class_name objects
	 * If the storage space already exists, it is updated without losing data
	 *
	 * @return boolean true if storage was created or updated, false if it was already up-to-date
	 */
	public static function createStorage(string $class_name) : bool
	{
		return self::current()->createStorage($class_name);
	}

	//--------------------------------------------------------------------------------------- current
	public static function current(Data_Link $set_current = null) : ?Data_Link
	{
		/** @var $data_link Data_Link */
		$data_link = self::pCurrent($set_current);
		return $data_link;
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * Delete an object from current data link
	 *
	 * If object was originally read from data source, corresponding data will be overwritten.
	 * If object was not originally read from data source, nothing is done and returns false.
	 *
	 * @param $object object Object to delete from data source
	 * @return boolean true if deleted
	 * @see Data_Link::delete()
	 */
	public static function delete(object $object) : bool
	{
		return self::current()->delete($object);
	}

	//------------------------------------------------------------------------------------ disconnect
	/**
	 * Disconnect an object from current data link
	 *
	 * @param $object              object  Object to disconnect from data source
	 * @param $load_linked_objects boolean If true, load linked objects before disconnect
	 * @see Data_Link::disconnect()
	 */
	public static function disconnect(object $object, bool $load_linked_objects = false) : void
	{
		self::current()->disconnect($object, $load_linked_objects);
	}

	//-------------------------------------------------------------------------------------- distinct
	/** Gets a DAO distinct option, used to return only distinct (different) values */
	public static function distinct() : Option\Distinct
	{
		return new Option\Distinct();
	}

	//------------------------------------------------------------------------------------ doublePass
	/** Gets as DAO double-pass option, used to enable double-pass optimization on read queries */
	public static function doublePass() : Option\Double_Pass
	{
		return new Option\Double_Pass();
	}

	//--------------------------------------------------------------------------------------- exclude
	/**
	 * This option enables to write all properties but those properties list values to the DAO
	 *
	 * Use this for optimizations and to avoid overridden writes if you are sure of what properties
	 * should not being written
	 *
	 * @param $properties string[]|...string
	 * @return Option\Exclude
	 */
	public static function exclude(array|string ...$properties) : Option\Exclude
	{
		return new Option\Exclude(func_get_args());
	}

	//--------------------------------------------------------------------------------------- exhaust
	/**
	 * Ensure that the ORM loads all linked objects from the default data link
	 * This gets the value of all properties of the object
	 * This gets the value of all component sub-objects too, but not of linked objects, as it may be
	 * huge and get out of hand
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object         array|object
	 * @param $keep_composite boolean If false, composite objects are set to null, to avoid recursions
	 * @param $disconnect     boolean If true, all id are deleted
	 */
	public static function exhaust(
		array|object $object, bool $keep_composite = true, bool $disconnect = false
	) : void
	{
		if (is_array($object)) {
			foreach ($object as $one_object) {
				static::exhaust($one_object, $keep_composite, $disconnect);
			}
			return;
		}
		/** @noinspection PhpUnhandledExceptionInspection object */
		foreach ((new Reflection_Class($object))->getProperties() as $property) {
			if (!$keep_composite && Composite::of($property)?->value) {
				unset($object->$property);
			}
			else {
				/** @noinspection PhpUnhandledExceptionInspection comes from getProperties, is accessible */
				$value = $property->getValue($object);
				if ($property->isComponent()) {
					static::exhaust($value, $keep_composite, $disconnect);
				}
				elseif ($disconnect && is_object($value) && isset($value->id)) {
					unset($value->id);
				}
			}
			if ($disconnect && isset($object->{"id_$property"})) {
				unset($object->{"id_$property"});
			}
		}
		if ($disconnect && isset($object->id)) {
			unset($object->id);
		}
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Gets the data link identified by the $dao_identifier string
	 * If no data link matches $dao_identifier or if its empty, gets the current default data link
	 */
	public static function get(string $dao_identifier = null) : Data_Link
	{
		if (empty($dao_identifier) || !isset(self::$list[$dao_identifier])) {
			return self::current();
		}
		$dao = self::$list[$dao_identifier];
		if (is_array($dao)) {
			$class_name = $dao[Configuration::CLASS_NAME];
			unset($dao[Configuration::CLASS_NAME]);
			/** @noinspection PhpUnhandledExceptionInspection verified $class_name */
			$dao = self::$list[$dao_identifier] = Builder::create($class_name, [$dao]);
		}
		return $dao;
	}

	//--------------------------------------------------------------------------------------- getList
	/**
	 * Gets the list of data links
	 *
	 * @return Data_Link[] key is the data list identifier (text, or empty for the main data list)
	 */
	public static function getList() : array
	{
		return array_merge(['' => self::current()], self::$list);
	}

	//--------------------------------------------------------------------------- getObjectIdentifier
	/**
	 * Read an object's identifier, if known for current data link.
	 * A null value will be returned for an object that is not linked to current data link.
	 *
	 * If property name is set, the object property value identifier will be read instead of the
	 * object's identifier. This enables you to get the property value id without reading the object
	 * from the database.
	 */
	public static function getObjectIdentifier(?object $object, string $property_name = null) : mixed
	{
		$data_link = self::current();
		return ($object && ($data_link instanceof Identifier_Map))
			? $data_link->getObjectIdentifier($object, $property_name)
			: null;
	}

	//--------------------------------------------------------------------------------------- groupBy
	public static function groupBy(array|string $properties = null) : Option\Group_By
	{
		return new Option\Group_By($properties);
	}

	//---------------------------------------------------------------------------------------- having
	public static function having(array $conditions = []) : Option\Having
	{
		return new Option\Having($conditions);
	}

	//-------------------------------------------------------------------------------------------- is
	/**
	 * Returns true if object1 and object2 match the same stored object
	 *
	 * @param $object1 ?object
	 * @param $object2 ?object
	 * @param $strict  boolean If true, will consider @link object and no-@link object as different
	 * @return boolean
	 */
	public static function is(?object $object1, ?object $object2, bool $strict = false) : bool
	{
		return self::current()->is($object1, $object2, $strict);
	}

	//------------------------------------------------------------------------ isLinkedObjectModified
	public static function isLinkedObjectModified(object $object) : bool
	{
		return isset($object->_dao_modified_linked_object);
	}

	//------------------------------------------------------------------------------------------- key
	/**
	 * An option to choose what property value will be used as keys for Dao::readAll()
	 * or Dao::search() results
	 *
	 * @param $property_name callable|string|string[]
	 * @return Option\Key
	 */
	public static function key(array|callable|string $property_name) : Option\Key
	{
		return new Option\Key($property_name);
	}

	//----------------------------------------------------------------------------------------- limit
	/**
	 * Gets a DAO limit option, used to limit the number of read objects with Dao::readAll()
	 * or Dao::search()
	 *
	 * @example Dao::readAll('ITRocks\Framework\User', Dao::limit(2, 10));
	 * Will return 10 read users objects, starting with the second read user
	 * @example Dao::readAll('ITRocks\Framework\User', Dao::limit(10));
	 * Will return the 10 first read users objects
	 * @param $from  integer|null The offset of the first object to return
	 *               (or the maximum number of objects to return if $count is null)
	 * @param $count integer|null The maximum number of objects to return
	 * @return Option\Limit
	 */
	public static function limit(int $from = null, int $count = null) : Option\Limit
	{
		return new Option\Limit($from, $count);
	}

	//--------------------------------------------------------------------------------- linkClassOnly
	public static function linkClassOnly() : Option\Link_Class_Only
	{
		return new Option\Link_Class_Only();
	}

	//---------------------------------------------------------------------------- modifyLinkedObject
	/**
	 * Tells that an object has been modified since it was read from the DAO and should be written.
	 *
	 * This disables the Link_Class_Only Dao option automatically set by Write::writeCollection
	 *
	 * @param $object   object
	 * @param $modified boolean true to enable 'modified, force write' ; false to disable it
	 * @see Write::writeCollection
	 */
	public static function modifyLinkedObject(object $object, bool $modified = true) : void
	{
		if ($modified) {
			$object->_dao_modified_linked_object = true;
		}
		else {
			unset($object->_dao_modified);
		}
	}

	//------------------------------------------------------------------------------------------ only
	/**
	 * This option enables to write only some properties values to the DAO
	 *
	 * Use this for optimizations and to avoid overridden writes if you are sure of which properties
	 * have to be written
	 *
	 * @param $properties string[]|...string
	 * @return Option\Only
	 */
	public static function only(array|string ...$properties) : Option\Only
	{
		return new Option\Only(func_get_args());
	}

	//--------------------------------------------------------------------------------------- preLoad
	/**
	 * Preload objects from the data storage during the query
	 *
	 * For optimization purpose : this allows to get multiple linked objects in only one query.
	 *
	 * @param $properties string[]|...string
	 * @return Option\Pre_Load
	 */
	public static function preLoad(array|string ...$properties) : Option\Pre_Load
	{
		return new Option\Pre_Load(func_get_args());
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from current data link
	 *
	 * @param $identifier mixed|object<T>      Identifier for the object, or an object to re-read
	 * @param $class_name class-string<T>|null Class for read object. Useless if $identifier is an
	 *                                         object
	 * @return ?T an object of class objectClass, read from data source, or null if nothing found
	 * @see Data_Link::read()
	 * @template T
	 */
	public static function read(mixed $identifier, string $class_name = null) : ?object
	{
		return self::current()->read($identifier, $class_name);
	}

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * Read all objects of a given class from current data link
	 *
	 * @param $class_name class-string<T> class name of read objects
	 * @param $options    Option|Option[] some options for advanced read
	 * @return T[] a collection of read objects
	 * @see Data_Link::readAll()
	 * @template T
	 */
	public static function readAll(string $class_name, array|Option $options = []) : array
	{
		return self::current()->readAll($class_name, $options);
	}

	//---------------------------------------------------------------------------------------- remove
	/** Removes a data link which identifier is a string from the list of available data links */
	public static function remove(string $dao_identifier) : void
	{
		if (isset(self::$list[$dao_identifier])) {
			unset(self::$list[$dao_identifier]);
		}
	}

	//--------------------------------------------------------------------------------------- replace
	/**
	 * Replace a destination object with the source object into current data link
	 *
	 * The source object overwrites the destination object into the data source, even if the source
	 * object was not originally read from the data source.
	 * Warning: as destination object will stay independent of source object but also linked to the
	 * same data source identifier. You will still be able to write() either source or destination
	 * after call to replace().
	 *
	 * @param $destination object<T> Destination object
	 * @param $source      object<T> Source object
	 * @param $write       boolean true if the destination object must be immediately written
	 * @return T the resulting $destination object
	 * @see Data_Link::replace()
	 * @template T
	 */
	public static function replace(object $destination, object $source, bool $write = true) : object
	{
		return self::current()->replace($destination, $source, $write);
	}

	//----------------------------------------------------------------------------- replaceReferences
	/**
	 * Replace all references to $replaced by references to $replacement into the database.
	 * Already loaded objects will not be changed.
	 *
	 * @param $replaced    object<T>
	 * @param $replacement object<T>
	 * @return boolean true if replacement has been done, false if something went wrong
	 * @template T
	 */
	public static function replaceReferences(object $replaced, object $replacement) : bool
	{
		return self::current()->replaceReferences($replaced, $replacement);
	}

	//--------------------------------------------------------------------------------------- reverse
	/**
	 * Gets a DAO reverse option, to use as a column name for call to Dao::sort() or new Sort()
	 *
	 * @example
	 * $users = Dao::readAll(
	 *   ITRocks\Framework\User::class,
	 *   Dao::sort([Dao::reverse('birth_date'), 'first_name', 'last_name'])
	 * );
	 * @param $column_name string A single column name which we will reverse order.
	 * @return Option\Reverse
	 */
	public static function reverse(string $column_name) : Option\Reverse
	{
		return new Option\Reverse($column_name);
	}

	//-------------------------------------------------------------------------------------- rollback
	/**
	 * Rollback a transaction with the current data link (non-transactional SQL engines will do
	 * nothing and return null)
	 *
	 * @return ?boolean true if commit succeeds, false if error, null if not a transactional SQL
	 *                  engine
	 * @see Transactional::rollback()
	 */
	public static function rollback() : ?bool
	{
		$current = self::current();
		if ($current instanceof Transactional) {
			return $current->rollback();
		}
		return null;
	}

	//---------------------------------------------------------------------------------------- search
	/**
	 * Search objects from current data link
	 *
	 * It is highly recommended to instantiate the $what object using Search_Object::instantiate() in
	 * order to initialize all properties as unset and build a correct search object.
	 * If some properties are a not-loaded objects, the search will be done on the object identifier,
	 * without joins to the linked object.
	 * If some properties are loaded objects : if the object comes from a read, the search will be
	 * done on the object identifier, without join. If object is not linked to data-link, the search
	 * is done with the linked object as others search criterion.
	 *
	 * @param $what       array|object<T>|null Source object for filter, only set properties will be
	 *                                         used for search
	 * @param $class_name class-string<T>|null Must be set if is $what is a filter array instead of a
	 *                                         filter object
	 * @param $options    Option|Option[] Some options for advanced search
	 * @return T[] A collection of read objects
	 * @see Data_Link::search()
	 * @template T
	 */
	public static function search(
		array|object|null $what, string $class_name = null, array|Option $options = []
	) : array
	{
		return self::current()->search($what, $class_name, $options);
	}

	//------------------------------------------------------------------------------------- searchOne
	/**
	 * Search one object from current data link
	 *
	 * Same as search(), but expected result is one object only.
	 * It is highly recommended to use this search with primary keys properties values searches.
	 * If several result exist, only one will be taken, the first on the list (it may be random).
	 *
	 * @param $what       array|object<T>      Source object for filter, only set properties will be
	 *                                         used for search
	 * @param $class_name class-string<T>|null Must be set if is not a filter array
	 * @param $options    Option|Option[]      Some options for advanced search
	 * @return ?T the found object, or null if no object was found
	 * @see Data_Link::searchOne()
	 * @template T
	 */
	public static function searchOne(
		array|object $what, string $class_name = null, array|Option $options = []
	) : ?object
	{
		return self::current()->searchOne($what, $class_name, $options);
	}

	//---------------------------------------------------------------------------------------- select
	/**
	 * Read selected columns only from data source, using optional filter
	 *
	 * @param $class         class-string<T> class for the read object
	 * @param $properties    string[]|string|Func[] the list of the property paths : only those
	 *                       properties will be read.
	 * @param $filter_object array|object<T>|null source object for filter, set properties will be
	 *                       used for search. Can be an array associating properties names to matching
	 *                       search value too.
	 * @param $options       Option|Option[] some options for advanced search
	 * @return List_Data a list of read records. Each record values (it may be objects) are
	 *                       stored in the same order as columns.
	 * @template T
	 */
	public static function select(
		string $class, array|string $properties, array|object $filter_object = null,
		array|Option $options = []
	) : List_Data
	{
		return self::current()->select($class, $properties, $filter_object, $options);
	}

	//------------------------------------------------------------------------------------------- set
	/**
	 * Sets a data link or its configuration into the data links list
	 *
	 * @param $dao_identifier             string
	 * @param $data_link_or_configuration Data_Link|string[]
	 */
	public static function set(string $dao_identifier, array|Data_Link $data_link_or_configuration)
		: void
	{
		self::$list[$dao_identifier] = $data_link_or_configuration;
	}

	//------------------------------------------------------------------------------------------ sort
	/**
	 * Gets a DAO sort option, used to sort objects read with Dao::readAll() or Dao::search()
	 *
	 * @example
	 * $users = Dao::readAll(
	 *   ITRocks\Framework\User::class,
	 *   Dao::sort(['first_name', 'last_name', 'city.country.name'])
	 * );
	 * @param $columns string|string[]|null A single or several column names.
	 *                 If null, the value of #Sort or #Representative will be taken as defaults.
	 * @return Option\Sort
	 */
	public static function sort(array|string $columns = null) : Option\Sort
	{
		return new Option\Sort($columns);
	}

	//----------------------------------------------------------------------------------- storeNameOf
	/** Gets the store name for records typed as $class_name */
	public static function storeNameOf(object|string $class) : string
	{
		return self::current()->storeNameOf($class);
	}

	//------------------------------------------------------------------------------- storedAsForeign
	/**
	 * Returns true if a property will be stored into a foreign table record,
	 * or false if it is stored as a simple value
	 */
	public static function storedAsForeign(Reflection_Property $property) : bool
	{
		$type = $property->getType();
		return $type->isClass() && !$type->isDateTime() && !Store::of($property)->isString();
	}

	//------------------------------------------------------------------------------------- timeLimit
	/**
	 * an option to set a max execution time in seconds, used in Dao::select
	 * MySQL 5.7.4 introduces the ability to set server side execution time limits.
	 *
	 * @param $time_limit integer Data link query execution time limit in seconds
	 * @return Option\Time_Limit
	 */
	public static function timeLimit(int $time_limit = 0) : Option\Time_Limit
	{
		return new Option\Time_Limit($time_limit);
	}

	//------------------------------------------------------------------------------------- translate
	public static function translate() : Option\Translate
	{
		return new Option\Translate();
	}

	//-------------------------------------------------------------------------------------- truncate
	/**
	 * Truncates the data-set storing $class_name objects. All data is deleted.
	 */
	public static function truncate(string $class_name) : void
	{
		self::current()->truncate($class_name);
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write an object using current data link
	 *
	 * If object was originally read from data source, corresponding data will be overwritten
	 * If object was not originally read from data source nor linked to it using replace(), a new
	 * record will be written into data source using this object's data.
	 *
	 * @param $object  object<T> object to write into data source
	 * @param $options Option|Option[] some options for advanced write
	 * @return ?T the written object, if has been written
	 * @see Data_Link::write()
	 * @template T
	 */
	public static function write(object $object, array|Option $options = []) : ?object
	{
		return self::current()->write($object, $options);
	}

}
