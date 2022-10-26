<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Data_Link\Identifier_Map;
use ITRocks\Framework\Dao\Data_Link\Transactional;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Current;
use ITRocks\Framework\Tools\List_Data;
use ITRocks\Framework\Tools\List_Row;

/**
 * The Dao class enables direct access to the main Dao object of the application methods
 */
class Dao implements Configurable
{
	use Current { current as private pCurrent; }

	//------------------------------------------------------------------------------------ LINKS_LIST
	const LINKS_LIST = 'list';

	//----------------------------------------------------------------------------------------- $list
	/**
	 * The list of available and referenced DAO
	 *
	 * @var Data_Link[]
	 */
	private static $list;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct($configuration)
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
	/**
	 * Forces add of object to the data link : no update, even if there is an identifier
	 *
	 * @return Option\Add
	 */
	public static function add()
	{
		return new Option\Add();
	}

	//----------------------------------------------------------------------------------------- begin
	/**
	 * Begin a transaction with the current data link (non-transactional SQL engines will do nothing
	 * and return null)
	 *
	 * @return boolean|null true if begin succeeds, false if error, null if not a transactional SQL engine
	 * @see Transactional::begin()
	 */
	public static function begin()
	{
		$current = self::current();
		if ($current instanceof Transactional) {
			return $current->begin();
		}
		else {
			$current->after_commit = [];
			return null;
		}
	}

	//----------------------------------------------------------------------------------- cacheResult
	/**
	 * Cache where options and query result
	 * And use cached result if exists
	 *
	 * @return Option\Cache_Result
	 */
	public static function cacheResult()
	{
		return new Option\Cache_Result();
	}

	//---------------------------------------------------------------------------------- classNamesOf
	/**
	 * Gets the class name associated to a store set name
	 *
	 * @param $store_name string
	 * @return string[] Full class name with namespace
	 */
	public static function classNamesOf($store_name)
	{
		return self::current()->classNamesOf($store_name);
	}

	//---------------------------------------------------------------------------------------- commit
	/**
	 * Commit a transaction using the current data link (non-transactional SQL engines will do nothing
	 * and return null)
	 *
	 * @return boolean|null true if commit succeeds, false if error, null if not a transactional SQL
	 *                      engine
	 * @see Transactional::commit()
	 */
	public static function commit()
	{
		$current = self::current();
		if ($current instanceof Transactional) {
			return $current->commit();
		}
		else {
			$current->afterCommit();
			return null;
		}
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * Count the number of elements that match filter
	 *
	 * @param $what       object|string|array source object for filter, only set properties used
	 * @param $class_name string must be set if is $what is a filter array instead of a filter object
	 * @param $options    Option|Option[] array some options for advanced search
	 * @return integer
	 */
	public static function count($what, $class_name = null, $options = []) : int
	{
		return self::current()->count($what, $class_name, $options);
	}

	//------------------------------------------------------------------------------ createIfNoResult
	/**
	 * @return Option\Create_If_No_Result
	 */
	public static function createIfNoResult()
	{
		return new Option\Create_If_No_Result();
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
	public static function createStorage($class_name)
	{
		return self::current()->createStorage($class_name);
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Data_Link|null
	 * @return Data_Link|null
	 */
	public static function current(Data_Link $set_current = null) : Data_Link|null
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
	 * @param $object object object to delete from data source
	 * @return boolean true if deleted
	 * @see Data_Link::delete()
	 */
	public static function delete($object)
	{
		return self::current()->delete($object);
	}

	//------------------------------------------------------------------------------------ disconnect
	/**
	 * Disconnect an object from current data link
	 *
	 * @param $object              object object to disconnect from data source
	 * @param $load_linked_objects boolean if true, load linked objects before disconnect
	 * @see Data_Link::disconnect()
	 */
	public static function disconnect($object, $load_linked_objects = false)
	{
		self::current()->disconnect($object, $load_linked_objects);
	}

	//-------------------------------------------------------------------------------------- distinct
	/**
	 * Gets a DAO distinct option, used to return only distinct (different) values
	 */
	public static function distinct()
	{
		return new Option\Distinct();
	}

	//------------------------------------------------------------------------------------ doublePass
	/**
	 * Gets as DAO double-pass option, used to enable double-pass optimization on read queries
	 *
	 * @return Option\Double_Pass
	 */
	public static function doublePass()
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
	 * @param $properties string[]|string ...
	 * @return Option\Exclude
	 */
	public static function exclude($properties)
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
	 * @param $keep_composite bool if false, composite objects are set to null, to avoid recursions
	 * @param $disconnect     bool if true, all id are deleted
	 */
	public static function exhaust(
		array|object $object, bool $keep_composite = true, bool $disconnect = false
	) {
		if (is_array($object)) {
			foreach ($object as $one_object) {
				static::exhaust($one_object, $keep_composite, $disconnect);
			}
			return;
		}
		$set_accessible_false = false;
		/** @noinspection PhpUnhandledExceptionInspection object */
		foreach ((new Reflection_Class($object))->getProperties() as $property) {
			if (!$keep_composite && $property->getAnnotation('composite')->value) {
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
	 *
	 * If no data link matches $dao_identifier or if its empty, gets the current default data link
	 *
	 * @noinspection PhpDocMissingThrowsInspection verified $class_name configuration
	 * @param $dao_identifier string
	 * @return Data_Link
	 */
	public static function get($dao_identifier)
	{
		if (!empty($dao_identifier) && isset(self::$list[$dao_identifier])) {
			$dao = self::$list[$dao_identifier];
			if (is_array($dao)) {
				$class_name = $dao[Configuration::CLASS_NAME];
				unset($dao[Configuration::CLASS_NAME]);
				/** @noinspection PhpUnhandledExceptionInspection verified $class_name */
				$dao = self::$list[$dao_identifier] = Builder::create($class_name, [$dao]);
			}
		}
		else {
			$dao = self::current();
		}
		return $dao;
	}

	//--------------------------------------------------------------------------------------- getList
	/**
	 * Gets the list of data links
	 *
	 * @return Data_Link[] key is the data list identifier (text, or empty for the main data list)
	 */
	public static function getList()
	{
		return array_merge(['' => self::current()], self::$list);
	}

	//--------------------------------------------------------------------------- getObjectIdentifier
	/**
	 * Read an object's identifier, if known for current data link.
	 * A null value will be returned for an object that is not linked to current data link.
	 *
	 * If property name is set, the object property value identifier will be read instead of the
	 * object's identifier. This enable you to get the property value id without reading the object
	 * from the database.
	 *
	 * @param $object        object
	 * @param $property_name string
	 * @return mixed
	 */
	public static function getObjectIdentifier($object, $property_name = null)
	{
		$data_link = self::current();
		return (($data_link instanceof Identifier_Map) && is_object($object))
			? $data_link->getObjectIdentifier($object, $property_name)
			: null;
	}

	//--------------------------------------------------------------------------------------- groupBy
	/**
	 * @param $properties string[]|string|null
	 * @return Option\Group_By
	 */
	public static function groupBy($properties = null)
	{
		return new Option\Group_By($properties);
	}

	//---------------------------------------------------------------------------------------- having
	/**
	 * @param $conditions array
	 * @return Option\Having
	 */
	public static function having(array $conditions = [])
	{
		return new Option\Having($conditions);
	}

	//-------------------------------------------------------------------------------------------- is
	/**
	 * Returns true if object1 and object2 match the same stored object
	 *
	 * @param $object1 object
	 * @param $object2 object
	 * @param $strict  boolean if true, will consider @link object and non-@link object as different
	 * @return boolean
	 */
	public static function is($object1, $object2, $strict = false)
	{
		return self::current()->is($object1, $object2, $strict);
	}

	//------------------------------------------------------------------------ isLinkedObjectModified
	/**
	 * @param $object object
	 * @return boolean
	 * @see modify
	 */
	public static function isLinkedObjectModified($object)
	{
		return isset($object->_dao_modified_linked_object);
	}

	//------------------------------------------------------------------------------------------- key
	/**
	 * An option to choose what property value will be used as keys for Dao::readAll()
	 * or Dao::search() results
	 *
	 * @param $property_name callable|string|string[]
	 * @return Option\Key;
	 */
	public static function key($property_name)
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
	 * @param $from  integer The offset of the first object to return
	 *               (or the maximum number of objects to return if $count is null)
	 * @param $count integer The maximum number of objects to return
	 * @return Option\Limit
	 */
	public static function limit($from = null, $count = null)
	{
		return new Option\Limit($from, $count);
	}

	//--------------------------------------------------------------------------------- linkClassOnly
	/**
	 * @return Option\Link_Class_Only
	 */
	public static function linkClassOnly()
	{
		return new Option\Link_Class_Only();
	}

	//---------------------------------------------------------------------------- modifyLinkedObject
	/**
	 * Tells that an object has been modified since it was read from the DAO and should be written.
	 *
	 * This disable the Link_Class_Only Dao option automatically set by Write::writeCollection
	 *
	 * @param $object object
	 * @param $modified boolean true to enable 'modified, force write' ; false to disable it
	 * @see Write::writeCollection
	 */
	public static function modifyLinkedObject($object, $modified = true)
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
	 * Use this for optimizations and to avoid overridden writes if you are sure of what properties
	 * have to been written
	 *
	 * @param $properties string[]|string ...
	 * @return Option\Only
	 */
	public static function only($properties)
	{
		return new Option\Only(func_get_args());
	}

	//--------------------------------------------------------------------------------------- preLoad
	/**
	 * Pre-load objects from the data storage during the query
	 *
	 * For optimization purpose : this allows to get multiple linked objects in only one query.
	 *
	 * @param $properties string[]|string ...
	 * @return Option\Pre_Load
	 */
	public static function preLoad($properties)
	{
		return new Option\Pre_Load(func_get_args());
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from current data link
	 *
	 * @param $identifier integer|string|T identifier for the object, or an object to re-read
	 * @param $class_name class-string<T>|null class for read object. Useless if $identifier is object
	 * @return ?T an object of class objectClass, read from data source, or null if nothing found
	 * @see Data_Link::read()
	 * @template T
	 */
	public static function read(int|object|string $identifier, string $class_name = null) : ?object
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
	/**
	 * Removes a data link which identifier is a string from the list of available data links
	 *
	 * @param $dao_identifier int|string
	 */
	public static function remove(int|string $dao_identifier)
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
	 * @param $destination T destination object
	 * @param $source      T source object
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
	 * @param $replaced    T
	 * @param $replacement T
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
	 * @return boolean|null true if commit succeeds, false if error, null if not a transactional SQL
	 * engine
	 * @see Transactional::rollback()
	 */
	public static function rollback()
	{
		$current = self::current();
		if ($current instanceof Transactional) {
			return $current->rollback();
		}
		else {
			return null;
		}
	}

	//---------------------------------------------------------------------------------------- search
	/**
	 * Search objects from current data link
	 *
	 * It is highly recommended to instantiate the $what object using Search_Object::instantiate() in
	 * order to initialize all properties as unset and build a correct search object.
	 * If some properties are an not-loaded objects, the search will be done on the object identifier,
	 * without joins to the linked object.
	 * If some properties are loaded objects : if the object comes from a read, the search will be
	 * done on the object identifier, without join. If object is not linked to data-link, the search
	 * is done with the linked object as others search criterion.
	 *
	 * @param $what       object|array source object for filter, only set properties will be used for
	 *                    search
	 * @param $class_name string must be set if is $what is a filter array instead of a filter object
	 * @param $options    Option|Option[] some options for advanced search
	 * @return object[] a collection of read objects
	 * @see Data_Link::search()
	 */
	public static function search($what, $class_name = null, $options = [])
	{
		return self::current()->search($what, $class_name, $options);
	}

	//------------------------------------------------------------------------------------- searchOne
	/**
	 * Search one object from current data link
	 *
	 * Same as search(), but expected result is one object only.
	 * It is highly recommended to use this search with primary keys properties values searches.
	 * If several result exist, only one will be taken, the first on the list (may be random).
	 *
	 * @param $what       object|array source object for filter, only set properties will be used for
	 *                    search
	 * @param $class_name class-string<T> must be set if is not a filter array
	 * @param $options    Option|Option[] some options for advanced search
	 * @return T|null the found object, or null if no object was found
	 * @see Data_Link::searchOne()
	 * @template T
	 */
	public static function searchOne($what, $class_name = null, $options = [])
	{
		return self::current()->searchOne($what, $class_name, $options);
	}

	//---------------------------------------------------------------------------------------- select
	/**
	 * Read selected columns only from data source, using optional filter
	 *
	 * @param $class         string class for the read object
	 * @param $properties    string[]|string|Func[] the list of the property paths : only those
	 *                       properties will be read.
	 * @param $filter_object object|array source object for filter, set properties will be used for
	 *                       search. Can be an array associating properties names to matching
	 *                       search value too.
	 * @param $options       Option|Option[] some options for advanced search
	 * @return List_Data|List_Row[] a list of read records. Each record values (may be objects) are
	 *                       stored in the same order than columns.
	 */
	public static function select($class, $properties, $filter_object = null, $options = [])
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
	public static function set($dao_identifier, $data_link_or_configuration)
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
	 * @param $columns string|string[] A single or several column names.
	 *                 If null, the value of annotations 'sort' or 'representative' will be taken as
	 *                 defaults.
	 * @return Option\Sort
	 */
	public static function sort($columns = null)
	{
		return new Option\Sort($columns);
	}

	//----------------------------------------------------------------------------------- storeNameOf
	/**
	 * Gets the store name for records typed as $class_name
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function storeNameOf($class_name)
	{
		return self::current()->storeNameOf($class_name);
	}

	//------------------------------------------------------------------------------- storedAsForeign
	/**
	 * Returns true if a property will be stored into a foreign table record,
	 * or false if it's is stored as a simple value
	 *
	 * @param $property Reflection_Property
	 * @return boolean
	 */
	public static function storedAsForeign(Reflection_Property $property)
	{
		$type = $property->getType();
		return $type->isClass()
			&& !$type->isDateTime()
			&& in_array($property->getAnnotation(Store_Annotation::ANNOTATION)->value, [null, '']);
	}

	//------------------------------------------------------------------------------------- timeLimit
	/**
	 * an option to set a max execution time in seconds, used in Dao::select
	 * MySQL 5.7.4 introduces the ability to set server side execution time limits.
	 *
	 * @param $time_limit integer Data link query execution time limit in seconds
	 * @return Option\Time_Limit
	 */
	public static function timeLimit($time_limit = 0)
	{
		return new Option\Time_Limit($time_limit);
	}

	//------------------------------------------------------------------------------------- translate
	/**
	 * @return Option\Translate
	 */
	public static function translate()
	{
		return new Option\Translate();
	}

	//-------------------------------------------------------------------------------------- truncate
	/**
	 * Truncates the data-set storing $class_name objects
	 * All data is deleted
	 *
	 * @param $class_name string
	 */
	public static function truncate($class_name)
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
	 * @param $object  T object to write into data source
	 * @param $options Option|Option[] some options for advanced write
	 * @return T|null the written object, if has been written
	 * @see Data_Link::write()
	 * @template T
	 */
	public static function write(object $object, array|Option $options = []) : object|null
	{
		return self::current()->write($object, $options);
	}

}
