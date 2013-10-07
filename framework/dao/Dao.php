<?php
namespace SAF\Framework;

/**
 * The Dao class enables direct access to the main Dao object of the application methods
 */
abstract class Dao
{
	use Current { current as private pCurrent; }

	//----------------------------------------------------------------------------------------- $list
	/**
	 * The list of available and referenced DAO
	 *
	 * @var Data_Link[]
	 */
	private static $list;

	//----------------------------------------------------------------------------------------- begin
	/**
	 * Begin a transaction with the current data link (non-transactional SQL engines will do nothing and return null)
	 *
	 * @return boolean|null true if begin succeeds, false if error, null if not a transactional SQL engine
	 * @see Transactional_Data_Link::begin()
	 */
	public static function begin()
	{
		$current = self::current();
		if ($current instanceof Transactional_Data_Link) {
			return $current->begin();
		}
		else {
			return null;
		}
	}

	//----------------------------------------------------------------------------------- classNameOf
	/**
	 * Gets the class name associated to a store set name
	 *
	 * @param $store_name string
	 * @return string Full class name with namespace
	 */
	public static function classNameOf($store_name)
	{
		return self::current()->classNameOf($store_name);
	}

	//---------------------------------------------------------------------------------------- commit
	/**
	 * Commit a transaction using the current data link (non-transactional SQL engines will do nothing and return null)
	 *
	 * @return boolean|null true if commit succeeds, false if error, null if not a transactional SQL engine
	 * @see Transactional_Data_Link::commit()
	 */
	public static function commit()
	{
		$current = self::current();
		if ($current instanceof Transactional_Data_Link) {
			return $current->commit();
		}
		else {
			return null;
		}
	}

	//------------------------------------------------------------------------------------- configure
	/**
	 * Configure DAO with specific DAO link elements
	 *
	 * @param $configuration array
	 * @return array
	 */
	public static function configure($configuration)
	{
		if (isset($configuration["list"])) {
			foreach ($configuration["list"] as $dao_identifier => $dao_configuration) {
				$class_name = $dao_configuration["class"];
				unset($dao_configuration["class"]);
				self::set($dao_identifier, new $class_name($dao_configuration));
			}
			unset($configuration["list"]);
		}
		return $configuration;
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

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Data_Link
	 * @return Data_Link
	 */
	public static function current(Data_Link $set_current = null)
	{
		return self::pCurrent($set_current);
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

	//------------------------------------------------------------------------------------------- get
	/**
	 * Get the data link identified by the $dao_identifier string
	 *
	 * @param $dao_identifier string
	 * @return Data_Link
	 */
	public static function get($dao_identifier)
	{
		return self::$list[$dao_identifier];
	}

	//--------------------------------------------------------------------------- getObjectIdentifier
	/**
	 * Read an object's identifier, if known for current data link
	 *
	 * A null value will be returned for an object that is not linked to current data link.
	 *
	 * @param $object object
	 * @return mixed
	 */
	public static function getObjectIdentifier($object)
	{
		$data_link = self::current();
		return ($data_link instanceof Identifier_Map_Data_Link)
			? $data_link->getObjectIdentifier($object)
			: null;
	}

	//------------------------------------------------------------------------------------------- key
	/**
	 * An option to choose what property value will be used as keys for Dao::readAll()
	 * or Dao::search() results
	 *
	 * @param $property_name string
	 * @return Dao_Key_Option;
	 */
	public static function key($property_name)
	{
		return new Dao_Key_Option($property_name);
	}

	//----------------------------------------------------------------------------------------- limit
	/**
	 * Gets a DAO limit option, used to limit the number of read objects with Dao::readAll()
	 * or Dao::search()
	 *
	 * @example Dao::readAll('SAF\Framework\User', Dao::limit(2, 10));
	 * Will return 10 read users objects, starting with the second read user
	 * @example Dao::readAll('SAF\Framework\User', Dao::limit(10));
	 * Will return the 10 first read users objects
	 *
	 * @param $from  integer The offset of the first object to return
	 * (or the maximum number of objects to return if $count is null)
	 * @param $count integer The maximum number of objects to return
	 * @return Dao_Limit_Option
	 */
	public static function limit($from = null, $count = null)
	{
		return new Dao_Limit_Option($from, $count);
	}

	//------------------------------------------------------------------------------------------ only
	/**
	 * This option enables to write only some properties values to the DAO
	 *
	 * Use this for optimizations and to avoid overridden writes if you are sure of what properties
	 * have to been written
	 *
	 * @param $properties string[]|string
	 * @return Dao_Only_Option
	 */
	public static function only($properties)
	{
		return new Dao_Only_Option($properties);
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from current data link
	 *
	 * @param $identifier   mixed identifier for the object
	 * @param $object_class string class for read object
	 * @return object an object of class objectClass, read from data source, or null if nothing found
	 * @see Data_Link::read()
	 */
	public static function read($identifier, $object_class)
	{
		return self::current()->read($identifier, $object_class);
	}

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * Read all objects of a given class from current data link
	 *
	 * @param $class_name string class name of read objects
	 * @param $options    Dao_Option|Dao_Option[] some options for advanced read
	 * @return object[] a collection of read objects
	 * @see Data_Link::readAll()
	 */
	public static function readAll($class_name, $options = null)
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
	 * The source object overwrites the destination object into the data source, even if the source object was not originally read from the data source.
	 * Warning: as destination object will stay independent from source object but also linked to the same data source identifier. You will still be able to write() either source or destination after call to replace().
	 *
	 * @param $destination object destination object
	 * @param $source      object source object
	 * @return object the resulting $destination object
	 * @see Data_Link::replace()
	 */
	public static function replace($destination, $source)
	{
		return self::current()->replace($destination, $source);
	}

	//-------------------------------------------------------------------------------------- rollback
	/**
	 * Rollback a transaction with the current data link (non-transactional SQL engines will do nothing and return null)
	 *
	 * @return boolean|null true if commit succeeds, false if error, null if not a transactional SQL engine
	 * @see Transactional_Data_Link::rollback()
	 */
	public static function rollback()
	{
		$current = self::current();
		if ($current instanceof Transactional_Data_Link) {
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
	 * It is highly recommended to instantiate the $what object using Search_Object::instantiate() in order to initialize all properties as unset and build a correct search object.
	 * If some properties are an not-loaded objects, the search will be done on the object identifier, without joins to the linked object.
	 * If some properties are loaded objects : if the object comes from a read, the search will be done on the object identifier, without join. If object is not linked to data-link, the search is done with the linked object as others search criterion.
	 *
	 * @param $what       object|array source object for filter, only set properties will be used for search
	 * @param $class_name string must be set if is $what is a filter array instead of a filter object
	 * @param $options    Dao_Option|Dao_Option[] some options for advanced search
	 * @return object[] a collection of read objects
	 * @see Data_Link::search()
	 */
	public static function search($what, $class_name = null, $options = null)
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
	 * @param $what       object|array source object for filter, only set properties will be used for search
	 * @param $class_name string must be set if is not a filter array
	 * @return object | null the found object, or null if no object was found
	 * @see Data_Link::searchOne()
	 */
	public static function searchOne($what, $class_name = null)
	{
		return self::current()->searchOne($what, $class_name);
	}

	//---------------------------------------------------------------------------------------- select
	/**
	 * Read selected columns only from data source, using optional filter
	 *
	 * @param $class         string class for the read object
	 * @param $columns       string[] the list of the columns names : only those properties will be read. You can use "column.sub_column" to get values from linked objects from the same data source.
	 * @param $filter_object object|array source object for filter, set properties will be used for search. Can be an array associating properties names to corresponding search value too.
	 * @param $options       Dao_Option|Dao_Option[] some options for advanced search
	 * @return List_Data a list of read records. Each record values (may be objects) are stored in the same order than columns.
	 */
	public static function select($class, $columns, $filter_object = null, $options = null)
	{
		return self::current()->select($class, $columns, $filter_object, $options);
	}

	//------------------------------------------------------------------------------------------- set
	/**
	 * Sets a data link into the data links list
	 *
	 * @param $dao_identifier string
	 * @param $data_link      Data_Link
	 */
	public static function set($dao_identifier, Data_Link $data_link)
	{
		self::$list[$dao_identifier] = $data_link;
	}

	//------------------------------------------------------------------------------------------ sort
	/**
	 * Gets a DAO sort option, used to sort objects read with Dao::readAll() or Dao::search()
	 *
	 * @example
	 * $users = Dao::readAll(
	 *   'SAF\Framework\User',
	 *   Dao::sort(array("first_name", "last_name", "city.country.name"))
	 * );
	 *
	 * @param $columns string|string[] A single or several column names.
	 * If null, the value of annotations "sort" or "representative" will be taken as defaults.
	 * @return Dao_Sort_Option
	 */
	public static function sort($columns = null)
	{
		return new Dao_Sort_Option($columns);
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

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write an object using current data link
	 *
	 * If object was originally read from data source, corresponding data will be overwritten
	 * If object was not originally read from data source nor linked to it using replace(), a new
	 * record will be written into data source using this object's data.
	 *
	 * @param $object  object object to write into data source
	 * @param $options Dao_Option[]|Dao_Option some options for advanced write
	 * @return object the written object
	 * @see Data_Link::write()
	 */
	public static function write($object, $options = array())
	{
		return self::current()->write($object, $options);
	}

}
