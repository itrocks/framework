<?php
namespace SAF\Framework;

abstract class Data_Link
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct data link using parameters array
	 *
	 * Parameters will depend on each data link type.
	 *
	 * @param array $parameters
	 */
	abstract public function __construct($parameters);

	//---------------------------------------------------------------------------------------- delete
	/**
	 * Delete an object from data source
	 *
	 * If object was originally read from data source, corresponding data will be overwritten.
	 * If object was not originally read from data source, nothing is done and returns false.
	 *
	 * @param  object $object object to delete from data source
	 * @return bool true if deleted
	 */
	abstract public function delete($object);

	//--------------------------------------------------------------------------- getStoredProperties
	/**
	 * Returns the list of properties of class $class that are stored into data link
	 *
	 * If data link stores properties not existing into $class, they are listed too, as if they where official properties of $class, but they storage object is a Dao_Column and not a Reflection_Property.
	 *
	 * @param string | Reflection_Class $class
	 * @return multitype:Reflection_Property | multitype:Dao_Column
	 */
	abstract public function getStoredProperties($class);

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from data source
	 *
	 * @param  object $identifier   identifier for the object
	 * @param  string $class class for read object
	 * @return object an object of class objectClass, read from data source, or null if nothing found
	 */
	abstract public function read($identifier, $class);

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * Read all objects of a given class from data source
	 *
	 * @param  string   $class class for read objects
	 * @return multitype:object a collection of read objects
	 */
	abstract public function readAll($class);

	//--------------------------------------------------------------------------------------- replace
	/**
	 * Replace a destination object with the source object into the data source
	 *
	 * The source object overwrites the destination object into the data source, even if the source object was not originally read from the data source.
	 * Warning: as destination object will stay independent from source object but also linked to the same data source identifier. You will still be able to write() either source or destination after call to replace().
	 *
	 * @param  object $destination destination object
	 * @param  object $source source object
	 * @return object the resulting $destination object
	 */
	abstract public function replace($destination, $source);

	//---------------------------------------------------------------------------------------- search
	/**
	 * Search objects from data source
	 *
	 * It is highly recommended to instantiate the $what object using Search_Object::instantiate() in order to initialize all properties as unset and build a correct search object.
	 * If some properties are an not-loaded objects, the search will be done on the object identifier, without joins to the linked object.
	 * If some properties are loaded objects : if the object comes from a read, the search will be done on the object identifier, without join. If object is not linked to data-link, the search is done with the linked object as others search criterion.
	 *
	 * @param  object $what source object for filter, only set properties will be used for search
	 * @return multitype:object a collection of read objects
	 */
	abstract public function search($what);

	//------------------------------------------------------------------------------------- searchOne
	/**
	 * Search one object from data source
	 *
	 * Same as search(), but expected result is one object only.
	 * It is highly recommended to use this search with primary keys properties values searches.
	 * If several result exist, only one will be taked, the first on the list (may be random). 
	 *
	 * @param object $what source object for filter, only set properties will be used for search
	 * @return object | null the found object, or null if no object was found
	 */
	public function searchOne($what)
	{
		$result = $this->search($what);
		return $result ? $result[0] : null;
	}

	//---------------------------------------------------------------------------------------- select
	/**
	 * Read selected columns only from data source, using optional filter
	 *
	 * @param  string $class class for the read object
	 * @param  array  $columns the list of the columns names : only those properties will be read.
	 * You can use "column.sub_column" to get values from linked objects from the same data source.
	 * @param  object $filter_object source object for filter, not-null properties will be used for search
	 * @return multitype:mixed a list of read records.
	 *   Each record values (may be objects) are stored in the same order than columns.
	 */
	abstract public function select($class, $columns, $filter_object = null);
	
	//----------------------------------------------------------------------------------------- write
	/**
	 * Write an object into data source
	 *
	 * If object was originally read from data source, corresponding data will be overwritten
	 * If object was not originally read from data source nor linked to it using replace(), a new
	 * record will be written into data source using this object's data.
	 *
	 * @param  object $object object to write into data source
	 * @return object the written object
	 */
	abstract public function write($object);

}
