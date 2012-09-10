<?php
namespace SAF\Framework;

abstract class Dao
{

	//------------------------------------------------------------------------------------ $data_link
	/**
	 * The current / main data link to use into your application
	 *
	 * @var Data_Link
	 */
	private static $data_link;

	//----------------------------------------------------------------------------------------- begin
	/**
	 * Begin a transaction with the current data link (non-transactional SQL engines will do nothing and return null)
	 *
	 * @return boolean | null true if begin succeeds, false if error, null if not a transactional SQL engine
	 * @see Transactional_Data_Link::begin()
	 */
	public static function begin()
	{
		if (Dao::$data_link instanceof Transactional_Data_Link) {
			return Dao::$data_link->begin();
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
		if (Dao::$data_link instanceof Transactional_Data_Link) {
			return Dao::$data_link->commit();
		}
		else {
			return null;
		}
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * Delete an object from current data link
	 *
	 * If object was originally read from data source, corresponding data will be overwritten.
	 * If object was not originally read from data source, nothing is done and returns false.
	 *
	 * @param  object $object object to delete from data source
	 * @return bool true if deleted
	 * @see Data_Link::delete()
	 */
	public static function delete($object)
	{
		return dao::$data_link->delete($object);
	}

	//----------------------------------------------------------------------------------- getDataLink
	/**
	 * Get current data link object 
	 *
	 * @return Data_Link
	 */
	public static function getDataLink()
	{
		return Dao::$data_link;
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from current data link
	 *
	 * @param  object $identifier   identifier for the object
	 * @param  string $object_class class for read object
	 * @return object an object of class objectClass, read from data source, or null if nothing found
	 * @see Data_Link::read()
	 */
	public static function read($value, $object_class)
	{
		return Dao::$data_link->read($value, $object_class);
	}

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * Read all objects of a given class from current data link
	 *
	 * @param  string   $object_class class for read objects
	 * @return multitype:object a collection of read objects
	 * @see Data_Link::readAll()
	 */
	public static function readAll($object_class)
	{
		return Dao::$data_link->readAll($object_class);
	}

	//--------------------------------------------------------------------------------------- replace
	/**
	 * Replace a destination object with the source object into current data link
	 *
	 * The source object overwrites the destination object into the data source, even if the source object was not originally read from the data source.
	 * Warning: as destination object will stay independent from source object but also linked to the same data source identifier. You will still be able to write() either source or destination after call to replace().
	 *
	 * @param  object $destination destination object
	 * @param  object $source source object
	 * @return object the resulting $destination object
	 * @see Data_Link::replace()
	 */
	public static function replace($destination, $source)
	{
		return Dao::$data_link->replace($destination, $source);
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
		if (Dao::$data_link instanceof Transactional_Data_Link) {
			return Dao::$data_link->rollback();
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
	 * @param  object $what source object for filter, only set properties will be used for search
	 * @return multitype:object a collection of read objects
	 * @see Data_Link::search()
	 */
	public static function search($what)
	{
		return Dao::$data_link->search($what);
	}

	//------------------------------------------------------------------------------------- searchOne
	/**
	 * Search one object from current data link
	 *
	 * Same as search(), but expected result is one object only.
	 * It is highly recommended to use this search with primary keys properties values searches.
	 * If several result exist, only one will be taked, the first on the list (may be random). 
	 *
	 * @param object $what source object for filter, only set properties will be used for search
	 * @return object | null the found object, or null if no object was found
	 * @see Data_Link::searchOne()
	 */
	public static function searchOne($what)
	{
		return Dao::$data_link->searchOne($what);
	}

	//----------------------------------------------------------------------------------- setDataLink
	/**
	 * Set current default data link to a given Data_Link object
	 *
	 * @param Data_Link $data_link
	 */
	public static function setDataLink($data_link)
	{
		Dao::$data_link = $data_link;
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write an object using current data link
	 *
	 * If object was originally read from data source, corresponding data will be overwritten
	 * If object was not originally read from data source nor linked to it using replace(), a new
	 * record will be written into data source using this object's data.
	 *
	 * @param  object $object object to write into data source
	 * @return object the written object
	 * @see Data_Link::write()
	 */
	public static function write($object)
	{
		Dao::$data_link->write($object);
	}

}
