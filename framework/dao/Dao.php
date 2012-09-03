<?php

abstract class Dao
{

	/**
	 * @var Data_Link
	 */
	private static $data_link;

	//---------------------------------------------------------------------------------- beginUpdates
	public static function beginUpdates()
	{
		if (method_exists(Dao::$data_link, "begin")) {
			Dao::$data_link->begin();
		}
	}

	//--------------------------------------------------------------------------------- commitUpdates
	public static function commitUpdates()
	{
		if (method_exists(Dao::$data_link, "commit")) {
			Dao::$data_link->commit();
		}
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * @param Object $object
	 */
	public static function delete($object)
	{
		return dao::$data_link->delete($object);
	}

	//----------------------------------------------------------------------------------- getDataLink
	/**
	 * @return Data_Link
	 */
	public static function getDataLink()
	{
		return Dao::$data_link;
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * @param mixed  $id
	 * @param string $object_class
	 */
	public static function read($value, $object_class)
	{
		return Dao::$data_link->read($value, $object_class);
	}

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * @param  string $object_class
	 * @return Object[]
	 */
	public static function readAll($object_class)
	{
		return Dao::$data_link->readAll($object_class);
	}

	//--------------------------------------------------------------------------------------- replace
	/**
	 * @param  Object $destination
	 * @param  Object $source
	 * @return Object source (replaced by destination)
	 */
	public static function replace($destination, $source)
	{
		return Dao::$data_link->replace($destination, $source);
	}

	//---------------------------------------------------------------------------------------- search
	/**
	 * @param  Object $what
	 * @return Object[]
	 */
	public static function search($what)
	{
		return Dao::$data_link->search($what);
	}

	//----------------------------------------------------------------------------------- searchFirst
	/**
	 * @param  Object $what
	 * @return Object
	 */
	public static function searchFirst($what)
	{
		return Dao::$data_link->searchFirst($what);
	}

	//----------------------------------------------------------------------------------- setDataLink
	/**
	 * @param  Data_Link $data_link
	 */
	public static function setDataLink($data_link)
	{
		Dao::$data_link = $data_link;
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * @param Object $object
	 */
	public static function write($object)
	{
		Dao::$data_link->write($object);
	}

}
