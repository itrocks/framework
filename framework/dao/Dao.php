<?php
namespace SAF\Framework;

abstract class Dao
{
	use Current { current as private pCurrent; }

	//----------------------------------------------------------------------------------------- begin
	/**
	 * Begin a transaction with the current data link (non-transactional SQL engines will do nothing and return null)
	 *
	 * @return boolean | null true if begin succeeds, false if error, null if not a transactional SQL engine
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

	//---------------------------------------------------------------------------------------- commit
	/**
	 * Commit a transaction using the current data link (non-transactional SQL engines will do nothing and return null)
	 *
	 * @return boolean | null true if commit succeeds, false if error, null if not a transactional SQL engine
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
	 * @return bool true if deleted
	 * @see Data_Link::delete()
	 */
	public static function delete($object)
	{
		return self::current()->delete($object);
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

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from current data link
	 *
	 * @param $identifier object   identifier for the object
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
	 * @param $object_class string   class for read objects
	 * @return object[] a collection of read objects
	 * @see Data_Link::readAll()
	 */
	public static function readAll($object_class)
	{
		return self::current()->readAll($object_class);
	}

	//--------------------------------------------------------------------------------------- replace
	/**
	 * Replace a destination object with the source object into current data link
	 *
	 * The source object overwrites the destination object into the data source, even if the source object was not originally read from the data source.
	 * Warning: as destination object will stay independent from source object but also linked to the same data source identifier. You will still be able to write() either source or destination after call to replace().
	 *
	 * @param $destination object destination object
	 * @param $source object source object
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
	 * @return boolean | null true if commit succeeds, false if error, null if not a transactional SQL engine
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
	 * @param $what       mixed source object for filter, only set properties will be used for search
	 * @param $class_name string must be set if is not a filter array
	 * @return object[] a collection of read objects
	 * @see Data_Link::search()
	 */
	public static function search($what, $class_name = null)
	{
		return self::current()->search($what, $class_name);
	}

	//------------------------------------------------------------------------------------- searchOne
	/**
	 * Search one object from current data link
	 *
	 * Same as search(), but expected result is one object only.
	 * It is highly recommended to use this search with primary keys properties values searches.
	 * If several result exist, only one will be taked, the first on the list (may be random).
	 *
	 * @param $what object source object for filter, only set properties will be used for search
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
	 * @param $class string class for the read object
	 * @param $columns array  the list of the columns names : only those properties will be read. You can use "column.sub_column" to get values from linked objects from the same data source.
	 * @param $filter_object mixed source object for filter, set properties will be used for search. Can be an array associating properties names to corresponding search value too.
	 * @return mixed[] a list of read records. Each record values (may be objects) are stored in the same order than columns.
	 */
	public static function select($class, $columns, $filter_object = null)
	{
		return self::current()->select($class, $columns, $filter_object);
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
	 * @param $object object object to write into data source
	 * @return object the written object
	 * @see Data_Link::write()
	 */
	public static function write($object)
	{
		return self::current()->write($object);
	}

}
