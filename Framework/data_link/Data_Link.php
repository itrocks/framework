<?php

interface Data_Link
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct data link using parameters
	 *
	 * @param array $parameters
	 */
	public function __construct($parameters);

	//---------------------------------------------------------------------------------------- delete
	/**
	 * Delete an object from data source
	 *
	 * If object was originally read from data source, corresponding data will be overwritten
	 * If object was not originally read from data source, nothing is done.
	 *
	 * @param  Object  $object object to delete from data source
	 * @return bool true if deleted
	 */
	public function delete($object);

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from data source
	 *
	 * @param  Object $identifier   identifier for the object
	 * @param  string $object_class class for read object
	 * @return Object an object of class objectClass, read from data source
	 */
	public function read($identifier, $object_class);

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * Read all objects from data source
	 *
	 * @param  string   $object_class class for read objects
	 * @return Object[] a collection of read objects
	 */
	public function readAll($object_class);

	//--------------------------------------------------------------------------------------- replace
	/**
	 * Replace a destination object with the source object into the data source
	 * The source object overwrites the destination object into the data source, even if the source
	 * object was not originally read from the data source.
	 *
	 * Warning: as destination object will stay independent from source object but also linked to the
	 * same data source identifier. You will still be able to write() either source or destination
	 * after call to replace().
	 *
	 * @param  Object $destination destination object
	 * @param  Object $source source object
	 * @return Object the resulting $destination object
	 */
	public function replace($destination, $source);

	//---------------------------------------------------------------------------------------- search
	/**
	 * Search objects from data source
	 *
	 * @param  Object $what source object for filter, non-null properties will be used for search
	 * @return Object[] a collection of read objects
	 */
	public function search($what);

	//---------------------------------------------------------------------------------------- select
	/**
	 * Read selected columns only from data source, using optional filter
	 *
	 * @param  string $objectClass class for the base read object
	 * @param  array  $columns the list of the columns names.
	 *   You can use "column.sub_column" to get values from linked objects from the same data source.
	 * @param  Object $filter_object source object for filter,
	 *   non-null properties will be used for search
	 * @return Object[] a list of read records.
	 *   Each record values are stored in the same order than columns.
	 */
	public function select($object_class, $columns, $filter_object = null);
	
	//----------------------------------------------------------------------------------------- write
	/**
	 * Write an object into data source
	 *
	 * If object was originally read from data source, corresponding data will be overwritten
	 * If object was not originally read from data source, or linked to it using replace(), a new
	 * record will be written into data source using this object's data.
	 *
	 * @param  Object $object object to write into data source
	 * @return Object the written object
	 */
	public function write($object);

}
