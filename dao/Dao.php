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
			return null;
		}
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
			return null;
		}
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * Count the number of elements that match filter
	 *
	 * @param $what       object|array source object for filter, only set properties will be used
	 * @param $class_name string must be set if is $what is a filter array instead of a filter object
	 * @return integer
	 */
	public static function count($what, $class_name = null)
	{
		return self::current()->count($what, $class_name);
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
	 * @param $set_current Data_Link
	 * @return Data_Link
	 */
	public static function current(Data_Link $set_current = null)
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
	 * @param $object object object to disconnect from data source
	 * @see Data_Link::disconnect()
	 */
	public static function disconnect($object)
	{
		return self::current()->disconnect($object);
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
		/** @var $exclude Option\Exclude */
		$exclude = (new Reflection_Class(Option\Exclude::class))->newInstanceArgs(func_get_args());
		return $exclude;
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Gets the data link identified by the $dao_identifier string
	 *
	 * If no data link matches $dao_identifier or if its empty, gets the current default data link
	 *
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
	 * @param $properties string[]|string
	 * @return Option\Group_By
	 */
	public static function groupBy($properties)
	{
		return new Option\Group_By($properties);
	}

	//------------------------------------------------------------------------------------------- key
	/**
	 * An option to choose what property value will be used as keys for Dao::readAll()
	 * or Dao::search() results
	 *
	 * @param $property_name string|string[]
	 * @return Option\Key;
	 */
	public static function key($property_name)
	{
		return new Option\Key($property_name);
	}

	//-------------------------------------------------------------------------------------------- is
	/**
	 * Returns true if object1 and object2 match the same stored object
	 *
	 * @param $object1 object
	 * @param $object2 object
	 * @return boolean
	 */
	public static function is($object1, $object2)
	{
		return self::current()->is($object1, $object2);
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
	 *
	 * @param $from  integer The offset of the first object to return
	 * (or the maximum number of objects to return if $count is null)
	 * @param $count integer The maximum number of objects to return
	 * @return Option\Limit
	 */
	public static function limit($from = null, $count = null)
	{
		return new Option\Limit($from, $count);
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
		/** @var $only Option\Only */
		$only = (new Reflection_Class(Option\Only::class))->newInstanceArgs(func_get_args());
		return $only;
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from current data link
	 *
	 * @param $identifier integer|object identifier for the object, or an object to re-read
	 * @param $class_name string class for read object. Useless if $identifier is an object
	 * @return object an object of class objectClass, read from data source, or null if nothing found
	 * @see Data_Link::read()
	 */
	public static function read($identifier, $class_name)
	{
		return self::current()->read($identifier, $class_name);
	}

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * Read all objects of a given class from current data link
	 *
	 * @param $class_name string class name of read objects
	 * @param $options    Option|Option[] some options for advanced read
	 * @return object[] a collection of read objects
	 * @see Data_Link::readAll()
	 */
	public static function readAll($class_name, $options = [])
	{
		return self::current()->readAll($class_name, $options);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Removes a data link which identifier is a string from the list of available data links
	 *
	 * @param $dao_identifier string
	 */
	public static function remove($dao_identifier)
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
	 * Warning: as destination object will stay independent from source object but also linked to the
	 * same data source identifier. You will still be able to write() either source or destination
	 * after call to replace().
	 *
	 * @param $destination object destination object
	 * @param $source      object source object
	 * @param $write       boolean true if the destination object must be immediately written
	 * @return object the resulting $destination object
	 * @see Data_Link::replace()
	 */
	public static function replace($destination, $source, $write = true)
	{
		return self::current()->replace($destination, $source, $write);
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
	public static function replaceReferences($replaced, $replacement)
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
	 *
	 * @param $column_name string A single column name which we will reverse order.
	 * @return Option\Reverse
	 */
	public static function reverse($column_name)
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
	 * @param $class_name string must be set if is not a filter array
	 * @param $options    Option|Option[] some options for advanced search
	 * @return object | null the found object, or null if no object was found
	 * @see Data_Link::searchOne()
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
	 *         stored in the same order than columns.
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
	 *
	 * @param $columns string|string[] A single or several column names.
	 * If null, the value of annotations 'sort' or 'representative' will be taken as defaults.
	 * @return Option\Sort
	 */
	public static function sort($columns = null)
	{
		return new Option\Sort($columns);
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
	 * @param $object  object object to write into data source
	 * @param $options Option|Option[] some options for advanced write
	 * @return object the written object
	 * @see Data_Link::write()
	 */
	public static function write($object, $options = [])
	{
		return self::current()->write($object, $options);
	}

}
